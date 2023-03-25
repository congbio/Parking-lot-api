<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Block>
 */
class BlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $arr =['4-16SLOT','16-34SLOT'];

        return [
            'parkingLotId'=>rand(1000000,1000002),
            'carType'=>$arr[rand(0,1)],
            'price'=>$this->faker->numberBetween($min = 15000, $max = 25000),
            'nameBlock'=>$this->faker->name(),
            'desc'=>$this->faker->sentence($nbWords = 6, $variableNbWords = true),
        ];
    }
}
