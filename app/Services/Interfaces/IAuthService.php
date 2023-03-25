<?php

namespace App\Services\Interfaces;

interface IAuthService
{
    /**
     * @return array return access token and refresh token. if login false, return []
     */
    public function login(string $email, string $password): array;

    /**
     * @return array decoded json of the token.
     */
    public function refreshToken(string $token): array;
    public function getAccessAndRefreshToken(int $userId): array;

}
