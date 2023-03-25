<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{
    use HasFactory;
    protected $table = 'parking_slots';
    protected $fillable = ['blockId','slotName'];
    public function block()
    {
        return $this->belongsTo(\App\Models\Block::class,'blockId');
    }
    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class,'slotId');
    }
    protected $hidden = [
        'created_at', 'updated_at','deleted_at',
    ];
}
