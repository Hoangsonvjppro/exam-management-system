<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key_name',
        'value',
        'description',
    ];

    protected $primaryKey = 'id';

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key_name', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set (upsert) a setting value by key.
     */
    public static function set(string $key, mixed $value, ?string $description = null): static
    {
        return static::updateOrCreate(
            ['key_name' => $key],
            array_filter([
                'value'       => $value,
                'description' => $description,
            ], fn ($v) => $v !== null)
        );
    }
}
