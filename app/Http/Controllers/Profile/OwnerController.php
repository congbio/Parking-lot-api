<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Clound\CloudinaryStorage;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
use App\Models\User;
use App\Models\UserParkingLot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OwnerController extends Controller
{
    /**
     * Update the user's profile.
     *
     * @OA\Post(
     *     path="/api/owner/create/{id}",
     *     summary="become owner parking lot",
     *     tags={"Space owner"},
     *     operationId="becomeSpaceOwner",
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
     *                     property="phone",
     *                     type="integer",
     *                 ),@OA\Property(
     *                     property="_method",
     *                     type="string",
     *                      example="PUT"
     *                 ),
     *                 @OA\Property(
     *                     property="businessScale",
     *                     type="string",
     *                     example="local"
     *                 ),
     *                 @OA\Property(
     *                     property="imageCardIdBef",
     *                     type="file",
     *                 ),
     *                 @OA\Property(
     *                     property="imageCardIdAft",
     *                     type="file",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     ),
     * security={ {"passport":{}}}
     * 
     * )
     */
    public function becomeSpaceOwner(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|digits:10',
            'businessScale' => 'required|in:local,business',
            'imageCardIdBef' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'imageCardIdAft' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        $user = User::findOrFail($id);
        $user->phone = $request->input('phone');
        $user->areaType = $request->input('businessScale');
        $image = $request->file('imageCardIdBef');
        if ($request->hasFile('imageCardIdBef')) {
            $linkImage = CloudinaryStorage::upload($image->getRealPath(), $image->getClientOriginalName(), 'account/cardId/Bef');
            $user->imageCardIdBef = $linkImage;
        }
        $image = $request->file('imageCardIdAft');
        if ($request->hasFile('imageCardIdAft')) {
            $linkImage = CloudinaryStorage::upload($image->getRealPath(), $image->getClientOriginalName(), 'account/cardId/Aft');
            $user->imageCardIdAft = $linkImage;
        }
        $user->role = 'owner';
        $user->save();
        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }

  


    public function parkingLotDashboard($parkingLotId)
    {
        

        return response()->json([
            'revenue' => $revenue,
        ], 200);
    }
}
