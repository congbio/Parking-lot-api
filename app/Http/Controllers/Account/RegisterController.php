<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\OTPRegisterRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\Interfaces\IAccountService;
use App\Services\Interfaces\IMailService;
use App\Services\Interfaces\IOTPService;
use App\Services\Interfaces\IRedisService;
use App\Services\Interfaces\MailType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __construct(
        private readonly IAccountService $accountService,
        private  readonly  IOTPService $otpService,
        private readonly IRedisService $redisService,
        private readonly IMailService $mailService

    ) {}
    /**
     * @OA\Post(
     ** path="/api/account/register", tags={"Account"}, summary="Register", operationId="register",
     *  @OA\Parameter(name="fullName",in="query",example ="cong",required=true, @OA\Schema( type="string" )),
     *  @OA\Parameter(name="email", in="query",example ="cong@yopmail.com" ,required=true, @OA\Schema(type="string")),
     *   @OA\Parameter( name="password", in="query",example ="12345678", required=true, @OA\Schema(type="string")),
     *   @OA\Parameter( name="password_confirmation", in="query", example ="12345678", required=true, @OA\Schema(type="string")),
     *   @OA\Response( response=201, description="Success",@OA\MediaType(mediaType="application/json",)),
     *   @OA\Response( response=401, description="Unauthenticated"
     *   ),
     *   
     *)
     **/
    public function register(RegisterRequest $request)
    {
        $userData = $request->validated();
        $otp = rand(100000, 999999);
        $user = new User;
        $user['email'] = $userData['email'];

        $this->otpService->sendOTP($user, $otp);

        $this->redisService->setOtp($user['email'], $otp);
        $this->redisService->setInfoRegis($userData, $otp);
        return "check code";
    }
     /**
     * @OA\Post(
     ** path="/api/account/confirm-registration", tags={"Account"}, summary="confirm otp to create accont", operationId="confirmRegistration",
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
    public function confirmRegistration(OTPRegisterRequest $request)
    {
        $userData = $request->validated();

        $optConfirm = $this->redisService->getOtp($userData['email']);

        if ($optConfirm == null) {
            return $this->responseError(
                "Email not sign up !",
                Response::HTTP_BAD_REQUEST,
            );
        } elseif ($userData['otp'] == $optConfirm) {
            $user =  $this->redisService->getInfoRegis($userData['otp']);
            $accData = $this->accountService->register((array)$user);
            $this->redisService->deleteOtp($user->email);
            $this->redisService->deleteInfor($userData['otp']);

            $this->mailService->sendMail(MailType::WELCOME_MAIL, ['email' => $userData['email']]);
            return $this->responseSuccessWithData(
                "Create a new account successfully!",
                $accData,
                Response::HTTP_CREATED
            );
        } else {
            return $this->responseError(
                "OTP Invalid!",
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
