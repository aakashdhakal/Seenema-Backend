<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();

            // Relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category')->nullable();

            // Video Metadata
            $table->decimal('rating', 2, 1)->nullable(); // e.g. 4.5
            $table->string('thumbnail_path')->nullable();
            $table->string('content_rating')->nullable(); // e.g. 'PG', 'R', etc.

            $table->integer('duration')->unsigned()->default(0); // in seconds

            // Status & Encoding
            $table->enum('status', ['processing', 'ready', 'failed'])->default('processing');


            // BOLA / Adaptive Bitrate Fields
            $table->string('video_codec')->nullable();
            $table->string('audio_codec')->nullable();
            $table->decimal('frame_rate', 5, 2)->nullable();
            $table->json('resolutions')->nullable(); // Stores array of {resolution, bitrate, etc.}

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};