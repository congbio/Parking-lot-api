<?php

namespace Database\Factories;

use App\Models\ParkingLot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserParkingLot>
 */
class UserParkingLotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     
    public function definition()
    {
        
        $owner = User::where('role', 'owner')->inRandomOrder()->first();
        $userId = $owner ? $owner->id : null;
        return [
            'userId' => $userId ?: null,
            'parkingId' => 1000000,
        ];
    }
}
