    <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notifications') && !Schema::hasColumn('notifications', 'request_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->unsignedBigInteger('request_id')->nullable()->after('type')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'request_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropColumn('request_id');
            });
        }
    }
};
