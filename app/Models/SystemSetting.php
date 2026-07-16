<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'is_encrypted'];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public static function getValue(string $group, string $key, ?string $default = null): ?string
    {
        $setting = self::query()->where('group', $group)->where('key', $key)->first();

        if (! $setting || $setting->value === null) {
            return $default;
        }

        return $setting->is_encrypted ? Crypt::decryptString($setting->value) : $setting->value;
    }

    public static function setValue(string $group, string $key, ?string $value, bool $encrypted = false): void
    {
        self::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            [
                'value' => $value === null || $value === '' ? null : ($encrypted ? Crypt::encryptString($value) : $value),
                'is_encrypted' => $encrypted,
            ],
        );
    }
}
