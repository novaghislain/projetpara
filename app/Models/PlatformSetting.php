<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }
    
    /**
     * Get a setting value, properly casted based on its type.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        switch ($setting->type) {
            case 'boolean':
                return (bool) $setting->value;
            case 'integer':
                return (int) $setting->value;
            case 'json':
                return is_string($setting->value) ? json_decode($setting->value, true) : $setting->value;
            default:
                return (string) $setting->value;
        }
    }
    
    /**
     * Set a setting value.
     */
    public static function setValue(string $key, $value, string $type = 'string', string $description = null)
    {
        $setting = self::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->type = $type;
        if ($description) {
            $setting->description = $description;
        }
        $setting->save();
        
        return $setting;
    }
}
