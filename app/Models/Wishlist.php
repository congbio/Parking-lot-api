<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;
    protected $table = 'wishlists';
    protected $fillable = ['userId', 'parkingLotId'];
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function parkingLots()
    {
        return $this->belongsToMany(ParkingLot::class, 'parkingLotId');
    }
}
