<?php

namespace Database\Factories\Users;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\PlusUser>
 */
class PlusUserFactory extends Factory
{
    protected $genderId;

    public function __construct($count = null,
                                ?Collection $states = null,
                                ?Collection $has = null,
                                ?Collection $for = null,
                                ?Collection $afterMaking = null,
                                ?Collection $afterCreating = null,
        $connection = null,
                                ?Collection $recycle = null,
                                bool $expandRelationships = true)
    {
        $this->genderId = DB::table('genders')->pluck('id');
        parent::__construct(    $count,
            $states,
            $has,
            $for,
            $afterMaking,
            $afterCreating,
            $connection,
            $recycle,
            $expandRelationships);
    }
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lastname' => fake()->lastName(),
            'birthday' => fake()->date('Y-m-d', '-30 years'),
            'gender_id' => $this->genderId->random(),
            'address' => fake()->address()

        ];
    }

}
