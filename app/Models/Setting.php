<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

/**
 * Class Setting
 *
 * @property string name
 * @property string value
 */
class Setting extends Model
{
    use HasFactory;

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
            'guestbook',
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
            'files',
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
    public static function setUserSettings(array $settings): void
    {
        self::$settings = $settings;
    }

    /**
     * Returns user settings
     *
     * @return array
     */
    public static function getUserSettings(): array
    {
        return self::$settings;
    }
}
