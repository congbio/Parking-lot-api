<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Message::class;

    public function definition()
    {
        $user1 = User::inRandomOrder()->first();
        $user2 = User::where('id', '!=', $user1->id)->inRandomOrder()->first();

        return [
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
            'content' => $this->faker->sentence(),
        ];
    }
}
