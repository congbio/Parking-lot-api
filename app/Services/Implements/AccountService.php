<?php
namespace App\Services\Implements;

use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AccountService implements  IAccountService {
    public function __construct(
        private readonly IUserRepository $userRepository
    )
    {}

    public function register(array $data): array
      {

          $data['fullName'] = $data['fullName'];
          $data['password'] = Hash::make($data['password']);
          $data['status'] = 1;
          $data['role'] = 'user';
          $data['avatar'] = 'https://res.cloudinary.com/di9pzz9af/image/upload/v1679615631/account/profile/icon-256x256_se6rre.png';
          $this->userRepository->create($data);
          return ["fullName" => $data["fullName"], "email" => $data["email"]];
      }
      public function resetPassword(array $data):mixed
      {
        $data['email'] = $data['email'];
        $data['password'] = Hash::make($data['password']);
        $this->userRepository->updateWhere(['email'=>$data['email']],['password'=>$data['password']]);
        return true;
      } 
      public function forgotPassword()
      {
          // TODO: Implement forgotPassword() method.
      }
      public function validateForgotPasswordToken(Request $request)
      {
          // TODO: Implement validateForgotPasswordToken() method.
      }

      public function  updateInformation()
      {
          // TODO: Implement updateInformation() method.
      }
}
