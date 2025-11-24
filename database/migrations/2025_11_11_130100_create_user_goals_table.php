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
        Schema::create('user_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['yearly', 'fiveYear', 'quarterly', 'monthly']);
            $table->enum('category', ['spiritual', 'personal', 'professional', 'health', 'relationships', 'ministry']);
            $table->date('target_date');
            $table->boolean('is_completed')->default(false);
            $table->integer('progress')->default(0); // 0-100
            $table->timestamps();
            
            // Index for better query performance
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'is_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_goals');
    }
};
