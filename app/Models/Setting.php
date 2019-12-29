<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Support\Facades\Cache;

/**
 * Class Setting
 *
 * @property string name
 * @property string value
 */
class Setting extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Custom settings
     *
     * @var array
     */
    private static $settings = [];

    /**
     * Возвращает список допустимых страниц настроек
     *
     * @return array
     */
    public static function getActions(): array
    {
        return [
            'mains',
            'mails',
            'info',
            'guestbooks',
            'news',
            'comments',
            'forums',
            'photos',
            'messages',
            'contacts',
            'loads',
            'blogs',
            'pages',
            'others',
            'protects',
            'prices',
            'adverts',
            'images',
            'stickers',
            'offers',
        ];
    }

    /**
     * Возвращает настройки сайта по ключу
     *
     * @return array данные
     */
    public static function getSettings(): array
    {
        try {
            $settings = Cache::rememberForever('settings', static function () {
                $settings = Setting::query()->pluck('value', 'name')->all();
                return array_map(static function ($value) {
                    if (is_numeric($value)) {
                        return strpos($value, '.') === false ? (int) $value : (float) $value;
                    }

                    if ($value === '') {
                        return null;
                    }

                    return $value;
                }, $settings);
            });
        } catch (Exception $e) {
            $settings = [];
        }

        return $settings;
    }

    /**
     * Sets user settings
     *
     * @param array $settings
     */
    public static function set(array $settings): void
    {
        self::$settings = $settings;
    }

    /**
     * Returns user settings
     *
     * @return array
     */
    public static function get(): array
    {
        return self::$settings;
    }
}
