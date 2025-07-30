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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable'); // user_id + user_type (for per-user notifications)
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // For advanced targeting
            $table->string('target_role')->nullable()->index(); // 'admin', 'user', etc.
            $table->boolean('is_broadcast')->default(false)->index(); // true if sent to all of a role
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};