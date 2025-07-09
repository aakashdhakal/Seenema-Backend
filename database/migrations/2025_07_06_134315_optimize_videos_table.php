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
        Schema::table('videos', function (Blueprint $table) {
            // Remove unnecessary columns that are not used in your current implementation
            $table->dropColumn([
                'video_codec',
                'audio_codec',
                'frame_rate'
            ]);

            // Add columns that your frontend upload form expects
            $table->string('language', 10)->default('en')->after('category');
            $table->string('visibility')->default('public')->after('content_rating'); // public, unlisted, private
            $table->year('release_year')->nullable()->after('visibility');

            // Modify existing columns for better data handling
            $table->text('description')->nullable()->change(); // Ensure it can handle long descriptions
            $table->string('content_rating', 10)->nullable()->change(); // Limit length for ratings like "PG-13"

            // Add indexes for better query performance
            $table->index(['category', 'status']);
            $table->index(['visibility', 'status']);
            $table->index('release_year');
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            // Re-add the removed columns
            $table->string('video_codec')->nullable()->after('status');
            $table->string('audio_codec')->nullable()->after('video_codec');
            $table->decimal('frame_rate', 5, 2)->nullable()->after('audio_codec');

            // Remove the added columns
            $table->dropColumn([
                'language',
                'visibility',
                'release_year',
                'trailer_url',
                'tags'
            ]);

            // Drop the indexes
            $table->dropIndex(['category', 'status']);
            $table->dropIndex(['visibility', 'status']);
            $table->dropIndex(['release_year']);
            $table->dropIndex(['language']);
        });
    }
};