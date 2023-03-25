<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParkingLot extends Model
{
    use HasFactory;
    protected $table = 'parking_lots';
    protected $fillable = ['nameParkingLot', 'address', 'images', 'openTime','address_latitude','address_longitude', 'endTime', 'desc'];
    public function userParkingLot()
    {
        return $this->hasMany(\App\Models\UserParkingLot::class,'parkingId');
    }
    public function blocks()
    {
        return $this->hasMany(\App\Models\Block::class,'parkingLotId');
    }
    public function comment()
    {
        return $this->hasMany(\App\Models\Comment::class,  'parkingId');
    }
    public function user()
    {
        return $this->hasOneThrough(User::class, UserParkingLot::class, 'parkingId', 'id', 'id', 'userId');
    }
}
