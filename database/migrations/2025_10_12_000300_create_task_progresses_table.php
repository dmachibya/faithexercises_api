<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('period');
            $table->string('period_key');
            $table->timestamp('done_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'task_id', 'period', 'period_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_progresses');
    }
};
