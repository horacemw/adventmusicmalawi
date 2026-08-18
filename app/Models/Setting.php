<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'group', 'cast', 'value', 'description', 'is_public'];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('settings.all', function () {
            return self::query()->get()->keyBy('key');
        });

        $setting = $all->get($key);
        if (!$setting) {
            return $default;
        }

        return match ($setting->cast) {
            'integer' => (int) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $setting->value, true),
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, string $cast = 'string', string $group = 'general'): self
    {
        $stored = match ($cast) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        return self::updateOrCreate(['key' => $key], [
            'value' => $stored,
            'cast' => $cast,
            'group' => $group,
        ]);
    }
}
