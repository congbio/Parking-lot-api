<?php

namespace Database\Factories;

use App\Models\ParkingLot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */



    public function definition()
    {
        $parkingLotId = ParkingLot::inRandomOrder()->first()->id;
        $userId = User::inRandomOrder()->first()->id;
        return [
            'userId' => $userId,
            'parkingId' => $parkingLotId,
            'content' => $this->faker->sentence(9, true),
            'ranting' => rand(1, 5),
            'created_at' => $this->faker->dateTime()->format('d-m-Y H:i:s'),
            'updated_at' => $this->faker->dateTime()->format('d-m-Y H:i:s'),
        ];
    }
}
