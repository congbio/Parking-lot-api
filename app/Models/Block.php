<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;
    protected $table = 'blocks';
    protected $fillable = ['parkingLotId','nameBlock','carType','price','desc'];
    public function parkingLot()
    {
        return $this->belongsTo(\App\Models\ParkingLot::class,"parkingLotId");
    }
    public function slots()
    {
        return $this->hasMany(\App\Models\ParkingSlot::class,'blockId');
    }
    protected $hidden = [
        'created_at', 'updated_at','deleted_at',
    ];
}
