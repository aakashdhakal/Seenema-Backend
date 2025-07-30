<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('watch_histories', function (Blueprint $table) {
            $table->boolean('continue_watching')->default(true)->after('finished_at');
        });
    }

    public function down(): void
    {
        Schema::table('watch_histories', function (Blueprint $table) {
            $table->dropColumn('continue_watching');
        });
    }
};