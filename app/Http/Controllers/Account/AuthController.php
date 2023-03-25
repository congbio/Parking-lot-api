<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Services\Interfaces\IAuthService;
use App\Utils\CookieGenerator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly IAuthService $authService) {}
    /**
         * @OA\Post(
         ** path="/api/auth/login",
         *   tags={"Account"},
         *   summary="Login",
         *   operationId="login",
         *   @OA\Parameter(
         *      name="email",
         *      in="query",
         *      required=true,
         *      @OA\Schema(
         *           type="string"
         *      )
         *   ),
         *   @OA\Parameter(
         *      name="password",
         *      in="query",
         *      required=true,
         *      @OA\Schema(
         *          type="string"
         *      )
         *   ),
         *   @OA\Response(
         *      response=200,
         *       description="Success",
         *   ),
         *   @OA\Response(
         *      response=401,
         *       description="Unauthenticated"
         *   ),
         *   @OA\Response(
         *      response=400,
         *      description="Bad Request"
         *   ),
         *   @OA\Response(
         *      response=404,
         *      description="not found"
         *   ),
         *      @OA\Response(
         *          response=403,
         *          description="Forbidden"
         *      )
         *)
         **/
    public function login(AuthRequest $request): JsonResponse
    {
        $email = $request->input("email");
        $password = $request->input("password");
        $loginData = $this->authService->login($email, $password);
        if ($loginData) {
            if(!$loginData['status']){
                return $this->responseErrorWithDetails(
                    "login.failed",
                    ["error" => "Your account can not login"],
                    Response::HTTP_UNAUTHORIZED
                );
            };
            ["accessToken" => $accessToken, "refreshToken" => $refreshToken, "uid" => $uid, "fullName" => $fullName] = $loginData;
            $response = $this->responseSuccessWithData(
                "login.successful",
                compact("accessToken", "uid", "fullName")
            );
            $refreshTokenCookie = CookieGenerator::generateRefreshTokenCookie($refreshToken);
            return $response->cookie($refreshTokenCookie);
        }
        return $this->responseErrorWithDetails(
            "login.failed",
            ["error" => "Email or password wrong!"],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
