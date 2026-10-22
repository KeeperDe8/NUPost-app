<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_requests', function (Blueprint $table) {
            $table->boolean('admin_seen')->default(false)->after('status');
        });

        Schema::table('request_comments', function (Blueprint $table) {
            $table->boolean('admin_seen')->default(false)->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('post_requests', function (Blueprint $table) {
            $table->dropColumn('admin_seen');
        });

        Schema::table('request_comments', function (Blueprint $table) {
            $table->dropColumn('admin_seen');
        });
    }
};