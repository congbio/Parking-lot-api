<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $arr=['admin','user','owner'];

        return [
            'email' => fake()->unique()->safeEmail(),
            'role'=>$arr[rand(0,2)],
            'fullName' =>fake()->name(),
            'avatar' =>'https://res.cloudinary.com/di9pzz9af/image/upload/v1679615631/account/profile/icon-256x256_se6rre.png',
            'password' =>Hash::make('12345'),
            'status' =>1
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
