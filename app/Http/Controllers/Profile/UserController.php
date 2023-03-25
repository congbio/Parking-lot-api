<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Clound\CloudinaryStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use App\Services\Interfaces\IProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly IProfile $profileService,
    ) {
    }
    /**
     * @OA\Get(
     ** path="/api/user/", tags={"Test"}, summary="get all user", operationId="getAllUser",
     *   @OA\Response( response=201, description="Success",@OA\MediaType(mediaType="application/json",)),
     *   @OA\Response( response=401, description="Unauthenticated"
     *   ),
     *   @OA\Response( response=400, description="Bad Request"
     *   ),
     *   @OA\Response( response=404, description="not found"
     *   ),
     *    @OA\Response( response=403, description="Forbidden"
     *      ),
     *  * security={ {"passport":{}}}
     *)
     **/
    public function getAllUser()
    {
        $userData = $this->profileService->getAllUser();
        return $userData;
    }
    /**
     * @OA\Get(
     ** path="/api/user/{id}/role", tags={"User"}, summary="check role user", operationId="getRole",
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getRole($id)
    {
        $role['role'] = User::find($id)->role;
        return $role;
    }
    public function showProfile($id)
    {
        $userInfo = $this->profileService->show($id);
        if ($userInfo == [null]) {
            return $this->responseError(
                "User not exit !",
                Response::HTTP_BAD_REQUEST,
            );
        } else {
            return $this->responseSuccessWithData(
                "Infomation of user",
                [$userInfo],
                Response::HTTP_ALREADY_REPORTED
            );
        }
    }
    /**
     * Update the user's profile.
     *
     * @OA\Post(
     *     path="/api/user/update/{id}",
     *     summary="Update user profile",
     *     tags={"User"},
     *     operationId="updateProfile",
     *     @OA\Parameter(
     *         name="id",
     *         description="User ID",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="fullName",
     *                     type="string",
     *                 ),@OA\Property(
     *                     property="_method",
     *                     type="string",
     *                      example="PUT"
     *                 ),
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="file"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     ),
     *         security={ {"passport":{}}}
     * 
     * )
     */
    public function updateProfile(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'fullName' => 'nullable|string|max:255|alpha|required_without_all:avatar',
        'avatar' => 'nullable|image|max:2048|required_without_all:fullName',
    ]);
    
    $validator->setCustomMessages([
        'required_without_all' => 'At least one of the values fields is required.',
    ]);
    
    if ($validator->fails()) {
        $errors = $validator->errors();
      
        return $errors->toArray();
    }
    $user = User::find($id);
    if ($request->has('fullName')&& $request->filled('fullName')) {
        $user->fullName = $request->input('fullName');
    }
    
    if ($request->hasFile('avatar')) {
        $file = $request->file('avatar');
        $linkImage = CloudinaryStorage::upload($file->getRealPath(), $file->getClientOriginalName(), 'account/profile');
        $user->avatar = $linkImage;
    }
   
    $user->save();
    
    return $this->responseSuccessWithData("Update successful", [$user], Response::HTTP_OK);
}
}
