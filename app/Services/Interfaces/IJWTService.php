<?php
namespace App\Services\Interfaces;

interface IJWTService {
    public function generateAccessToken(int $userId): string;
    public function generateRefreshToken(int $userId): string;
    public function verifyToken(string $token, TokenType $type): array;
}
