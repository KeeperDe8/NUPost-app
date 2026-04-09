<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('email');
            $table->string('otp_code', 6);
            $table->dateTime('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();
            $table->index('email');
            $table->index('otp_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};