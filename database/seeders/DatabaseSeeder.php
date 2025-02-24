<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'email' => 'john-doe@example.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ]);

        \App\Models\User::factory(10)->create();
        
        $projects = \App\Models\Project::factory(10)->create();
        \App\Models\User::each(function ($user) use ($projects) {
            $user->projects()->attach(
                $projects->random(rand(1, 5))->pluck('id')->toArray()
            );
        });

        // Call the AttributeSeeder
        $this->call(AttributeSeeder::class);
    }
}
