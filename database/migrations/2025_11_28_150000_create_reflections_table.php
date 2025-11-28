<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reflections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['text', 'audio', 'quote', 'verse'])->default('text');
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->string('author')->nullable(); // For quotes
            $table->string('reference')->nullable(); // For bible verses
            $table->date('scheduled_date')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reflections');
    }
};
