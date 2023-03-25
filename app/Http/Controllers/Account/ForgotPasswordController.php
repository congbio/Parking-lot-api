<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPassWordRequest;
use App\Models\User;
use App\Services\Interfaces\IAccountService;
use App\Services\Interfaces\IOTPService;
use App\Services\Interfaces\IRedisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly IAccountService $accountService,
        private  readonly  IOTPService $otpService,
        private readonly IRedisService $redisService,
    ) {
    }
    /**
     * @OA\Post(
     ** path="/api/password/sendCode", tags={"Account"}, summary="send code to reset account", operationId="sendCode",
     *  @OA\Parameter(name="email", in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Response( response=201, description="Success",@OA\MediaType(mediaType="application/json",)),
     *   @OA\Response( response=401, description="Unauthenticated"
     *   ),
     *   @OA\Response( response=400, description="Bad Request"
     *   ),
     *   @OA\Response( response=404, description="not found"
     *   ),
     *    @OA\Response( response=403, description="Forbidden"
     *      )
     *)
     **/
    public function sendCode(ForgotPasswordRequest $request): JsonResponse
    {
        $userData = $request->validated();
        $otp = rand(100000, 999999);

        $user = new User();
        $user['email'] = $userData['email'];
        $this->otpService->sendOTP($user, $otp);
        $this->redisService->setOtp($user['email'], $otp);
        $this->redisService->setInfoRegis($userData, $otp);


        return $this->responseSuccessWithData(
            "Check email to get code!",
            $userData,
            Response::HTTP_CREATED
        );
    }
    /**
     * @OA\Post(
     ** path="/api/password/confirm-reset", tags={"Account"}, summary="send code to reset account", operationId="checkCode",
     *  @OA\Parameter(name="otp",in="query",required=true, @OA\Schema( type="integer" )),
     *  @OA\Parameter(name="email", in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Response( response=201, description="Success",@OA\MediaType(mediaType="application/json",)),
     *   @OA\Response( response=401, description="Unauthenticated"
     *   ),
     *   @OA\Response( response=400, description="Bad Request"
     *   ),
     *   @OA\Response( response=404, description="not found"
     *   ),
     *    @OA\Response( response=403, description="Forbidden"
     *      )
     *)
     **/
    public function checkCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|integer',
            'email'=>'required',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();

        $opt = $dateData["otp"];
        $email = $dateData['email'];
        $optConfirm = $this->redisService->getOtp($email);
        if ($optConfirm == null) {
            return $this->responseError(
                "Email not fould !",
                Response::HTTP_BAD_REQUEST,
            );
        } elseif ($opt == $optConfirm) {
            $user =  $this->redisService->getInfoRegis($opt);

            $this->redisService->deleteOtp($user->email);
            $this->redisService->deleteInfor($opt);
            return $this->responseSuccessWithData(
                "OTP Matching ",
                ["email" => $email],
                Response::HTTP_OK,
            );
        } else {
            return $this->responseError(
                "OTP Invalid!",
                Response::HTTP_BAD_REQUEST
            );
        }
    }
    /**
     * @OA\Post(
     ** path="/api/password/reset", tags={"Account"}, summary="change password", operationId="resetPassword",
     *  @OA\Parameter(name="email", in="query", required=true, @OA\Schema(type="string")),
     *  @OA\Parameter(name="password", in="query", required=true, @OA\Schema(type="string")),
     *  @OA\Parameter(name="password_confirmation", in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Response( response=201, description="Success",@OA\MediaType(mediaType="application/json",)),
     *   @OA\Response( response=401, description="Unauthenticated"
     *   ),
     *   @OA\Response( response=400, description="Bad Request"
     *   ),
     *   @OA\Response( response=404, description="not found"
     *   ),
     *    @OA\Response( response=403, description="Forbidden"
     *      )
     *)
     **/
    public function resetPassword(ResetPassWordRequest $request): JsonResponse
    {

        $userData = $request->validated();
        $this->accountService->resetPassword($userData);
        return $this->responseSuccess("Change password success!", Response::HTTP_OK);
    }
}
