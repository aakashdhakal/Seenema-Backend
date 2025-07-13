<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            // Add a new status for when a file is being uploaded.
            // We need to get the current enum values and add the new one.
            $table->string('status')->change();
            \DB::statement("ALTER TABLE videos MODIFY COLUMN status ENUM('uploading', 'processing', 'ready', 'failed') NOT NULL DEFAULT 'uploading'");

            // Add a column to store the original filename for reassembly.
            $table->string('original_filename')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('original_filename');
            // Revert status column to its previous state
            \DB::statement("ALTER TABLE videos MODIFY COLUMN status ENUM('processing', 'ready', 'failed') NOT NULL DEFAULT 'processing'");
        });
    }
};