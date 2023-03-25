<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;

class User extends Model  implements Authenticatable
{

    use HasFactory, SoftDeletes, AuthenticatableTrait;
    protected $table = 'users';
    protected $fillable = ['email', 'fullName', 'role', 'avatar', 'password', 'status'];
    public function roleDFUser()
    {
        return $this->hasMany(\App\Models\RoleDFUser::class, "roleId");
    }
    public function booking()
    {
        return $this->hasMany(\App\Models\Booking::class, 'userId');
    }
    public function userParkingLots()
    {
        return $this->hasMany(\App\Models\UserParkingLot::class, 'userId');
    }
    public function comments()
    {
        return $this->hasMany(\App\Models\Comment::class, 'userId');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'userId');
    }
    

    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at','deleted_at',
    ];
    // Define the user's notifications relationship
    public function notifications()
    {
        return $this->hasMany(Notification::class,'userId');
    }
}
