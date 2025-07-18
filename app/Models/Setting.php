<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Support\Facades\Cache;

/**
 * Class Setting
 *
 * @property string $name
 * @property string $value
 */
class Setting extends BaseModel
{
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
            'feeds',
            'invitations',
            'boards',
            'votes',
            'seo',
        ];
    }

    /**
     * Возвращает настройки сайта по ключу
     */
    public static function getSettings(): array
    {
        try {
            return Cache::rememberForever('settings', static function () {
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
}
