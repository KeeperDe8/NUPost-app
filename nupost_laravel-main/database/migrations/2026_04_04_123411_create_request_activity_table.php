<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->string('actor');
            $table->text('action');
            $table->timestamps();
            $table->index('request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_activity');
    }
};