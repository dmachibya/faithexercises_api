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

            $summaries = [
                'Decide' => 'Here, you will learn the power of decisions and how they significantly contribute to starting your journey as a Christian or life in general... This exercise will help you begin today and never turn back.',
                'Forgive' => 'The power of forgiveness has an impact that earthly words may not fully explain... forgiveness is freedom, peace, love, rest, and a connection to God.',
                'Be grateful' => 'Thanking God for both small and big things acknowledges His blessings. If we do not learn to be grateful, we will never recognize the value of blessings released daily for us.',
                'Do not fear' => 'Fear is the foundation of a negative attitude that shuts doors of faith. These exercises will help cure excessive fear and build confidence in life and ministry.',
                'Be positive' => 'Cultivate positivity to grow faith. Stop complaining and learn to see life’s opportunities filled with God’s blessings; rejoice rather than sorrow as a lifestyle.',
                'Love' => 'The Bible teaches that love is a weapon for victory. Learn how to love yourself and others so that God’s love may manifest in your life.',
                'Help other' => 'Learn the secret of helping others (even with little) and how it blesses lives, brings inner peace, and strengthens faith over a dependency mindset.',
                'Acquit wealth' => 'Let go of anger and negative emotions that consume resources of time and energy; allow your mind to engage in progressive, God-honoring processes.',
                'Be humble' => 'Humility helps us relate to everyone and respect both humans and God. This virtue grows faith as we recognize our dependence on one another.',
                'Expect the second coming of Jesus' => 'Anticipating Jesus’ return transforms life; understanding this world is not our home elevates faith as we wait for our Lord to take us home.',
            ];

            $today = Carbon::today();

            foreach ($titles as $i => $title) {
                $exercise = Exercise::create([
                    'title' => $title,
                    'description' => null,
                    'summary' => $summaries[$title] ?? null,
                    'feature_image_url' => null,
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
