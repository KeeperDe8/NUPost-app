<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'first_name')) {
                    $table->string('first_name', 100)->nullable()->after('name');
                }
                if (!Schema::hasColumn('users', 'last_name')) {
                    $table->string('last_name', 100)->nullable()->after('first_name');
                }
            });
        }

        if (Schema::hasTable('post_requests')) {
            Schema::table('post_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('post_requests', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('request_id')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'first_name')) {
                    $table->dropColumn('first_name');
                }
                if (Schema::hasColumn('users', 'last_name')) {
                    $table->dropColumn('last_name');
                }
            });
        }

        if (Schema::hasTable('post_requests')) {
            Schema::table('post_requests', function (Blueprint $table) {
                if (Schema::hasColumn('post_requests', 'user_id')) {
                    $table->dropColumn('user_id');
                }
            });
        }
    }
};
