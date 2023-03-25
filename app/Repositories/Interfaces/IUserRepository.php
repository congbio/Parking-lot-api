<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface IUserRepository extends IRepository
{
    public function ban(int $userId): mixed;
    public function getInfo(int $userId): mixed;


}
