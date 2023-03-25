<?php

namespace App\Services\Implements;

use App\Repositories\Interfaces\IUserRepository;
use Illuminate\Support\Facades\Hash;

class Profile implements \App\Services\Interfaces\IProfile
{
    public function __construct(
        private readonly IUserRepository $userRepository
    )
    {}

    public function show(int $id):mixed
    {
        return $this->userRepository->getInfo($id);
    }
    public function editProfile(int $id,array $info)
    {
        $data['fullName'] = $info[0]['fullName'];
        $data['avatar'] = $info[0]['avatar'];
        $this->userRepository->update($id,$data);
        return ["fullName" => $data["fullName"], "avatar" => $data["avatar"]];
    }
    public function getAllUser(): mixed
    {
        return $this->userRepository->all();
    }
}
