<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->enum('role', ['admin', 'user'])->default('user')->after('email');
            $table->boolean('is_approved')->default(false)->after('role');
            $table->timestamp('approved_at')->nullable()->after('is_approved');
        });

        // Grandfather semua user yang sudah ada sebelum sistem approval ini dibuat,
        // supaya tidak ada yang tiba-tiba terkunci dari akunnya sendiri.
        DB::table('users')->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        // Jadikan akun dengan email ini sebagai admin pertama.
        DB::table('users')
            ->where('email', 'admin@mail.co.id')
            ->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'is_approved', 'approved_at']);
        });
    }
};
