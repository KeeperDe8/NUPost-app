<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Self-heal: Ensure RequestComment model file exists on server disk
        $modelPath = app_path('Models/RequestComment.php');
        if (!file_exists($modelPath)) {
            @mkdir(dirname($modelPath), 0755, true);
            @file_put_contents($modelPath, "<?php\n\nnamespace App\Models;\n\nuse Illuminate\Database\Eloquent\Model;\n\nclass RequestComment extends Model\n{\n    protected \$table = 'request_comments';\n    protected \$guarded = [];\n}\n");
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
