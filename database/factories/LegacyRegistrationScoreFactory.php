<?php

namespace Database\Factories;

use App\Models\LegacyRegistrationScore;
use Illuminate\Database\Eloquent\Factories\Factory;

class LegacyRegistrationScoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LegacyRegistrationScore::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'matricula_id' => fn () => LegacyRegistrationFactory::new()->create(),
        ];
    }
}
