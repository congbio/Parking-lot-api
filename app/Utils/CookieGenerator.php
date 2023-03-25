<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\Cookie;

class CookieGenerator
{
    public static function generateRefreshTokenCookie(string $refreshToken): Cookie {
        return cookie(
            "refresh-token",
            $refreshToken,
            env("REFRESH_TOKEN_EXPIRED_TIME")/(60*1000) + 7*60,
            "/api/",
            null,
            true
        );
    }
}
