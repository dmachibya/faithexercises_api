<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Exercise;
use App\Models\Task;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed the 10 specific Faith Exercises with some basic tasks
        if (Exercise::count() === 0) {
            $titles = [
                'Decide',
                'Forgive',
                'Be grateful',
                'Do not fear',
                'Be positive',
                'Love',
                'Help other',
                'Acquit wealth',
                'Be humble',
                'Expect the second coming of Jesus',
            ];

            $today = Carbon::today();

            foreach ($titles as $i => $title) {
                $exercise = Exercise::create([
                    'title' => $title,
                    'description' => null,
                    'sort_order' => $i + 1,
                ]);

                // Basic tasks per exercise
                // 1) Daily task with a 30-day duration window to drive streaks
                Task::create([
                    'exercise_id' => $exercise->id,
                    'title' => 'Daily Practice',
                    'description' => 'Complete the daily practice related to: ' . $title,
                    'schedule' => 'daily',
                    'duration_days' => 30,
                    'start_date' => $today->toDateString(),
                    'is_active' => true,
                    'sort_order' => 1,
                ]);

                // 2) Weekly reflection
                Task::create([
                    'exercise_id' => $exercise->id,
                    'title' => 'Weekly Reflection',
                    'description' => 'Review your progress this week for: ' . $title,
                    'schedule' => 'weekly',
                    'is_active' => true,
                    'sort_order' => 2,
                ]);

                // 3) One-time commitment
                Task::create([
                    'exercise_id' => $exercise->id,
                    'title' => 'Make a Commitment',
                    'description' => 'Set a clear commitment for how you will live out: ' . $title,
                    'schedule' => 'single',
                    'is_active' => true,
                    'sort_order' => 3,
                ]);
            }
        }
    }
}
