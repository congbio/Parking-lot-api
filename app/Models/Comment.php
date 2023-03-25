<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $table = 'comments';
    protected $fillable = ['userId','parkingId','content','ranting','created_at','update_at'];
    public function userParkingLot()
    {
        return $this->belongsTo(\App\Models\ParkingLot::class);
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
