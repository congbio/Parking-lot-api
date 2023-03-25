<?php

namespace App\Http\Middleware;

use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IAuthService;
use App\Services\Interfaces\IJWTService;
use App\Services\Interfaces\IRedisService;
use App\Services\Interfaces\TokenType;
use App\Traits\ApiResponse;
use App\Utils\CookieGenerator;
use App\Utils\RequestUtils;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LogicException;
use UnexpectedValueException;


class AuthMiddleware
{
    use ApiResponse;

    public function __construct(
        private readonly IJWTService   $jwtService,
        private readonly IRedisService $redisService,
        private readonly IAuthService  $authService,
        private readonly IUserRepository $userRepository
    )
    {}

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response|RedirectResponse|JsonResponse
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        $path = $request->path();
        if ($this->isRegister($path) || $this->isConfirmRegistration($path)||$this->isConfirmForgotPassword($path)|| $this->isForgotPassWord($path)|| $this->isResetPassword($path) || $this->isTest($path)){
            return $next($request);
        }
        if ($this->isLogin($path)) {
            $decodedRefreshToken = $this->checkRefreshToken($request);
            if ($decodedRefreshToken) {
                ["uid" => $userId] = $decodedRefreshToken;
                return $this->loginHandle($userId);
            }
            return $next($request);
        }
        // handle path /api/
        if ($this->checkAccessToken($request)) {
            return $next($request);
        }
        $decodedRefreshToken = $this->checkRefreshToken($request);
        if ($decodedRefreshToken) {
            ["uid" => $userId] = $decodedRefreshToken;
            $request->tokens = $this->authService->getAccessAndRefreshToken($userId);
            return $next($request);
        }

        return $this->responseErrorWithDetails(
            "re-login.required",
            ["error" => "Token invalid! Please re-login!"]
        );
    }

    private function isLogin(string $path): bool
    {
        return str_contains($path, "auth/login");
    }
    private function isRegister(string $path): bool {
        return str_contains($path, "account/register");
    }

    private function isConfirmRegistration(string $path): bool {
        return str_contains($path,"account/confirm-registration");
    }
    private function isConfirmForgotPassword(string $path): bool {
        return str_contains($path,"password/confirm-reset");
    }
    private function isForgotPassWord(string $path): bool {
        return str_contains($path, "password/email");
    }
    private function isResetPassword(string $path): bool {
        return str_contains($path,"password/reset");
    }
    private function isTest($path) {
        return str_contains($path, "test");
    }
    private function checkRefreshToken(Request $request): array|bool
    {
        $refreshToken = $request->cookie("refresh-token");
        if ($refreshToken) {
            try {
                $decodedRF = $this->jwtService->verifyToken($refreshToken, TokenType::REFRESH_TOKEN);
                ["uid" => $userId] = $decodedRF;
                $storedRefreshToken = $this->redisService->getRefreshTokenByUserId($userId);
                if ($storedRefreshToken == null || $refreshToken != $storedRefreshToken) {
                    return false;
                }
                return $decodedRF;
            } catch (UnexpectedValueException|LogicException $e) {
            }
        }
        return false;
    }
    private function checkAccessToken(Request $request): bool
    {
        $accessToken = RequestUtils::getAccessTokenFromRequest($request);
        if ($accessToken) {
            try {
                $this->jwtService->verifyToken($accessToken, TokenType::ACCESS_TOKEN);
                return true;
            } catch (UnexpectedValueException|LogicException $e) {
            }
        }
        return false;
    }
    /**
     * Handle an incoming request.
     *
     * @param int $userId
     * @return JsonResponse
     */
    private function loginHandle(int $userId): JsonResponse
    {
        // get new tokens
        ["accessToken" => $newAccessToken, "refreshToken" => $newRefreshToken] = $this->authService->getAccessAndRefreshToken($userId);
        $user = $this->userRepository->getInfo($userId);
        $response = $this->responseSuccessWithData(
            "login.successful.viaRF",
            ["accessToken" => $newAccessToken, "uid" => $userId, "fullName" => $user->fullName]
        );
        $refreshTokenCookie = CookieGenerator::generateRefreshTokenCookie($newRefreshToken);
        return $response->cookie($refreshTokenCookie);
    }

}
