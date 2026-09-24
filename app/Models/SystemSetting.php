<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'parametres_systeme';
    protected $guarded = [];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('cle', $key)->first();
        if (!$setting) return $default;

        return match ($setting->type) {
            'number' => is_numeric($setting->valeur) ? (float) $setting->valeur : $default,
            'boolean' => filter_var($setting->valeur, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->valeur, true) ?? $default,
            default => $setting->valeur,
        };
    }

    public static function setValue(string $key, mixed $value, string $type = 'string', ?string $description = null): static
    {
        $stored = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            default => (string) $value,
        };

        return static::query()->updateOrCreate(
            ['cle' => $key],
            ['valeur' => $stored, 'type' => $type, 'description' => $description]
        );
    }
}
