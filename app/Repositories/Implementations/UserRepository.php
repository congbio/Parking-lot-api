<?php

namespace App\Repositories\Implementations;

use App\Models\User;
use App\Repositories\Interfaces\IUserRepository;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements IUserRepository
{

    public function getModel(): string
    {
        return User::class;
    }

    public function ban(int $userId): mixed
    {
        return $this->model->find($userId)->trashed();
    }

    public function getInfo(int $userId): mixed
    {
        $info = $this->model->find($userId, ["id", "fullName", "email",'avatar']);
        return $info ? : null;
    }



}
