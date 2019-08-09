<?php

declare(strict_types=1);

namespace App\Models;

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
}
