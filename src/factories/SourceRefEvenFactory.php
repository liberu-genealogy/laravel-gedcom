<?php

namespace Database\Factories;

use App\Models\SourceRefEven;
use Illuminate\Database\Eloquent\Factories\Factory;

class SourceRefEvenFactory extends Factory
{
    public $faker;
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SourceRefEven::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'group' => $this->faker->word(),
            'gid'   => $this->faker->randomElement('1', '2'),
            'even'  => $this->faker->word(),
            'role'  => $this->faker->word(), 'created_at', 'updated_at',
        ];
    }
}
