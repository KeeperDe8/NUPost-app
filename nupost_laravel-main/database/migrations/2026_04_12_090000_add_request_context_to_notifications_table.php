<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'request_id')) {
                $table->unsignedBigInteger('request_id')->nullable()->after('user_id');
                $table->index('request_id');
            }

            if (!Schema::hasColumn('notifications', 'request_status')) {
                $table->string('request_status', 50)->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'request_status')) {
                $table->dropColumn('request_status');
            }

            if (Schema::hasColumn('notifications', 'request_id')) {
                $table->dropColumn('request_id');
            }
        });
    }
};
