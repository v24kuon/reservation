<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    /** @use HasFactory<\Database\Factories\SystemSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::query()->where('key', $key)->first();

        return $row?->value ?? $default;
    }

    public static function getJson(string $key, array $default = []): array
    {
        $value = static::get($key);
        if ($value === null || $value === '') {
            return $default;
        }
        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : $default;
    }

    public static function put(string $key, mixed $value, string $type = 'text', ?string $description = null): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value, 'type' => $type, 'description' => $description]
        );
    }
}
