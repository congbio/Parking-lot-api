<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\ParkingLot;

class ParkingLotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ParkingLot::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        return [
            'nameParkingLot' => $this->faker->name(),
            'address_latitude' => $this->faker->randomFloat(6, 16.0690, 16.0464),
            'address_longitude' => $this->faker->randomFloat(6, 108.2212, 108.2371),
            'address' => $this->faker->address(),
            'images' => json_encode(['https://m.media-amazon.com/images/I/510kGCsWt7L._SL1000_.jpg', 'https://m.media-amazon.com/images/I/510kGCsWt7L._SL1000_.jpg', 'https://m.media-amazon.com/images/I/510kGCsWt7L._SL1000_.jpg']),
            'openTime' => $this->faker->time(),
            'endTime' => $this->faker->time(),
            'desc' => $this->faker->sentence($nbWords = 6, $variableNbWords = true),
            'status' => $this->faker->boolean(),
        ];
    }
}
