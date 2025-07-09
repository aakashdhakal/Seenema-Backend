<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->foreignId('person_id')->constrained()->onDelete('cascade');
            $table->string('role'); // e.g., Director, Actor
            $table->string('credited_as')->nullable(); // The character name for an actor
            $table->timestamps();

            // Prevent duplicate entries
            $table->unique(['video_id', 'person_id', 'role', 'credited_as']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credits');
    }
};