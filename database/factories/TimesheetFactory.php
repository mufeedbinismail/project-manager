<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimesheetFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'task_name' => $this->faker->sentence,
            'date' => $this->faker->date('Y-m-d'),
            'hours' => $this->faker->numberBetween(1, 24),
        ];
    }
}
