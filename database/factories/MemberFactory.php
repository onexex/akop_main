<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'firstname' => $this->faker->firstName,
            'middlename' => $this->faker->optional()->firstName,
            'lastname' => $this->faker->lastName,
            'suffix' => $this->faker->optional()->suffix,

            'address_id' => $this->faker->optional()->numberBetween(1, 50),

            'taggedBy' => $this->faker->optional()->numberBetween(1, 10),
            'isTagged' => $this->faker->boolean(40), // 40% tagged

            'last_login_lat' => $this->faker->latitude(4.5, 21.5),  // PH range
            'last_login_long' => $this->faker->longitude(116.9, 126.6),
        ];
    }
}
