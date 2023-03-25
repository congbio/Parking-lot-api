<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
     
    public function definition()
    {
        $user = User::inRandomOrder()->first();

        return [
            'userId' => $user->id,
            'title'=>$this->faker->title(),
            'nameUserSend'=>$this->faker->name(),
            'data' =>json_encode([
                'reservation_id' => $this->faker->randomNumber(),
                'table_number' => $this->faker->randomNumber(),
                'guest_name' => $this->faker->name,
                'guest_email' => $this->faker->email,
            ]) ,
            'message' => $this->faker->sentence(),
            'type' => $this->faker->name,
            'image'=>'https://res.cloudinary.com/di9pzz9af/image/upload/v1677472834/account/profile/icon-256x256_o1oxjl.pn',
            'read' => false,
        ];
    }
}
