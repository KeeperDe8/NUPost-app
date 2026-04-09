<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id', 20)->nullable()->unique();
            $table->string('title')->nullable();
            $table->string('requester', 150)->nullable();
            $table->string('category', 100)->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->nullable();
            $table->string('status')->default('Pending Review');
            $table->text('description')->nullable();
            $table->string('platform', 255)->nullable();
            $table->text('caption')->nullable();
            $table->date('preferred_date')->nullable();
            $table->string('media_file', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_requests');
    }
};