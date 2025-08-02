<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('role');
            $table->date('dob')->nullable()->after('gender');
            $table->string('bio', 255)->nullable()->after('dob');
            $table->string('phone', 20)->nullable()->after('bio');
            $table->string('address', 255)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_picture',
                'role',
                'gender',
                'dob',
                'bio',
                'phone',
                'address',
            ]);
        });
    }
};