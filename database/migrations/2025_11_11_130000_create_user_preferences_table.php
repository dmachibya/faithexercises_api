<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('learning_frequency', ['daily', 'weekly'])->default('daily');
            $table->enum('journaling_frequency', ['daily', 'weekly'])->default('daily');
            $table->time('bible_study_time')->default('07:00');
            $table->time('prayer_time')->default('06:30');
            $table->time('meditation_time')->default('21:00');
            $table->json('learning_cues')->nullable(); // ['morning', 'afternoon', 'evening', 'after_dinner', 'before_bed']
            $table->json('journaling_cues')->nullable(); // ['morning', 'afternoon', 'evening', 'after_dinner', 'before_bed']
            $table->boolean('bible_study_reminder')->default(true);
            $table->boolean('prayer_reminder')->default(true);
            $table->boolean('meditation_reminder')->default(true);
            $table->timestamps();
            
            // Ensure one preference record per user
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
