<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Setting Model — Cấu hình hệ thống.
 *
 * @property int    $id
 * @property string $key_name     Khoá cấu hình
 * @property string $value        Giá trị
 * @property string $description  Mô tả
 */
class Setting extends Model
{
    protected $fillable = ['key_name', 'value', 'description'];

    /**
     * Lấy giá trị setting theo key.
     * Có cache tránh query lặp lại.
     *
     * @param string $key     Khoá cấu hình
     * @param mixed  $default Giá trị mặc định nếu không tìm thấy
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key_name', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Cập nhật hoặc tạo mới setting.
     */
    public static function setValue(string $key, string $value, ?string $description = null): static
    {
        return static::updateOrCreate(
            ['key_name' => $key],
            array_filter([
                'value' => $value,
                'description' => $description,
            ])
        );
    }
}
