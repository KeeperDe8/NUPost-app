<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Ensure the settings table exists automatically in the database.
     */
    public static function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('settings')) {
                Schema::create('settings', function (Blueprint $table) {
                    $table->id();
                    $table->string('key')->unique();
                    $table->longText('value')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // In case of restricted permissions or concurrent requests, fail gracefully
        }
    }

    /**
     * Retrieve a setting value by key, with fallback to default if not set.
     */
    public static function get(string $key, $default = null)
    {
        try {
            static::ensureTableExists();

            if (Schema::hasTable('settings')) {
                $setting = static::where('key', $key)->first();
                if ($setting && $setting->value !== null && $setting->value !== '') {
                    return $setting->value;
                }
            }
        } catch (\Throwable $e) {
            // Silently fall back to default
        }

        return $default;
    }

    /**
     * Set or update a setting value.
     */
    public static function set(string $key, $value): void
    {
        try {
            static::ensureTableExists();

            if (Schema::hasTable('settings')) {
                static::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        } catch (\Throwable $e) {
            // Fail gracefully
        }
    }
}
