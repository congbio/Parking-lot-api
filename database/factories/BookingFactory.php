<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $fromDate = "2023-01-01";
        $toDate = "2023-12-31";
        $from = "2023-01-01";
        $to = "2023-12-31";
        
        $startDateTime = $this->faker->dateTimeBetween($fromDate, $toDate);
        $endDateTime = $this->faker->dateTimeBetween($from, $to);
        
        // Make sure startDateTime is earlier than endDateTime
        if ($startDateTime > $endDateTime) {
            $tempDateTime = $startDateTime;
            $startDateTime = $endDateTime;
            $endDateTime = $tempDateTime;
        }
        
        return [
            'userId' => rand(1000000, 1000019),
            'slotId' => rand(100000000, 100000099),
            'licensePlate' => $this->faker->regexify('[0-9]{2}[A-Z]{1}-[0-9]{3}\.[0-9]{2}'),
            'bookDate' => $startDateTime->format("Y-m-d H:i:s"),
            'returnDate' => $endDateTime->format("Y-m-d H:i:s"),
            'payment' => $this->faker->numberBetween($min = 30000, $max = 600000),
        ];
    }
}
