<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->string('sender_role', 20)->default('admin');
            $table->string('sender_name');
            $table->text('message');
            $table->timestamps();
            $table->index('request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_comments');
    }
};