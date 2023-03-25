<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleDFUser extends Model
{
    use HasFactory;
    use HasFactory, SoftDeletes;
    protected $table = 'role_d_f_users';
    protected $fillable = ['userId','roleID'];
    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class);
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }



}
