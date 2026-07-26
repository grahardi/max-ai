<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_images', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('tool')->default('remove-background')->index();
            $table->string('original_path');
            $table->string('result_path')->nullable();
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('original_size')->nullable();
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])
                ->default('pending');
            $table->text('error_message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_images');
    }
};
