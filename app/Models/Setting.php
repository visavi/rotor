<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Class Setting
 *
 * @property string $name
 * @property string $value
 */
class Setting extends Model
{
    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'name';

    /**
     * The "type" of the primary key ID.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Возвращает список допустимых страниц настроек
     */
    public static function getActions(): array
    {
        return [
            'mains',
            'mails',
            'info',
            'comments',
            'messages',
            'pages',
            'others',
            'protects',
            'prices',
            'files',
            'stickers',
            'feeds',
            'seo',
        ];
    }

    /**
     * Процессный memo настроек (сбрасывается через flush)
     */
    private static ?array $settings = null;

    /**
     * Возвращает настройки сайта по ключу
     */
    public static function getSettings(): array
    {
        if (self::$settings !== null) {
            return self::$settings;
        }

        try {
            return self::$settings = Cache::rememberForever('settings', static function () {
                $settings = Setting::query()
                    ->pluck('value', 'name')
                    ->all();

                return array_map(static function ($value) {
                    if (is_numeric($value)) {
                        return ! str_contains($value, '.') ? (int) $value : (float) $value;
                    }

                    return $value === '' ? null : $value;
                }, $settings);
            });
        } catch (Exception) {
            return [];
        }
    }

    /**
     * Сбрасывает кеш и процессный memo настроек
     */
    public static function flush(): void
    {
        Cache::forget('settings');

        self::$settings = null;
    }
}
