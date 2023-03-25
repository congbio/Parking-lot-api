<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $table = 'bookings';
    protected $fillable = ['userId','slotId','bookDate','returnDate','payment','licensePlate'];
    public function slot()
    {
        return $this->belongsTo(\App\Models\ParkingSlot::class,'slotId');
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class,'userId');
    }
    protected $hidden = [
        'updated_at','deleted_at'
    ];
}
