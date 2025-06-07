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
            $table->timestamps();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('slug')->unique();
            $table->string('manifest_path')->nullable();
            $table->json('bitrates')->nullable();
            $table->json('segment_sizes')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->integer('duration')->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category')->default('default');
            $table->enum('status', ['processing', 'ready', 'failed'])->default('processing');
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
