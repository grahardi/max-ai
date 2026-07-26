<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_files', function (Blueprint $table) {
            $table->foreignId('folder_id')->nullable()->after('user_id')
                ->constrained('member_folders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('member_files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('folder_id');
        });
    }
};
