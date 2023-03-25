<?php
namespace App\Services\Interfaces;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;

interface IAccountService {
    public function register(array $data):array;
    public function forgotPassword();
    public function resetPassword(array $data):mixed;
    public function validateForgotPasswordToken(Request $request);
    public function updateInformation();
}
