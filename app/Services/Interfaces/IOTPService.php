<?php
namespace App\Services\Interfaces;
use App\Models\User;

interface IOTPService
{
    public function sendOTP(User $user, int $otp);
    public function validateOTP(string $email, int $otp);
}
