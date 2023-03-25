<?php

namespace Database\Factories;

use App\Models\ParkingLot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wishlist>
 */
class WishlistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
       
        $parkingLotId = ParkingLot::inRandomOrder()->first();
        return [
           'userId'=>1000000,
           'parkingLotId'=>$parkingLotId->id
        ];
    }
}
