<?php

namespace App\Services\Implements;

use App\Services\Interfaces\IJWTService;
use App\Services\Interfaces\TokenType;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService implements IJWTService
{
    const ALG = "HS512";
    public function generateAccessToken(int $userId): string
    {
        $key = env("ACCESS_TOKEN_SECRET");
        $payload = [
            "uid" => $userId,
            "exp" => date_create()->getTimestamp() + env("ACCESS_TOKEN_EXPIRED_TIME")
        ];
        return JWT::encode($payload, $key, JWTService::ALG);
    }

    public function generateRefreshToken(int $userId): string
    {
        $key = env("REFRESH_TOKEN_SECRET");
        $payload = [
            "uid" => $userId,
            "exp" => date_create()->getTimestamp() + env("REFRESH_TOKEN_EXPIRED_TIME")
        ];
        return JWT::encode($payload, $key, "HS512");
    }

    public function verifyToken(string $token, TokenType $type): array
    {
        switch ($type) {
            case TokenType::ACCESS_TOKEN: {
                $secret = env("ACCESS_TOKEN_SECRET");
                break;
            }
            case TokenType::REFRESH_TOKEN: {
                $secret = env("REFRESH_TOKEN_SECRET");
                break;
            }
            default: return [];
        }
        return (array)JWT::decode($token, new Key($secret, JWTService::ALG));
    }
}

