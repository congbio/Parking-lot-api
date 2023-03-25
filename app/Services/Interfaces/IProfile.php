<?php

namespace App\Services\Interfaces;

interface IProfile
{
    public function show(int $id):mixed;
    public function editProfile(int $id,array $info);
    public function getAllUser():mixed;

}
