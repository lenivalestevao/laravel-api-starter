<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Creates a demo account: demo@example.com / password
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
        ]);

        Project::factory()
            ->count(3)
            ->for($user)
            ->has(Task::factory()->count(5))
            ->create();

        Project::factory()->archived()->for($user)->create(['name' => 'Old website']);
    }
}
