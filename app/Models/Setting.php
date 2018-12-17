<?php

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
            'main',
            'mail',
            'info',
            'guest',
            'forum',
            'bookmark',
            'load',
            'blog',
            'page',
            'other',
            'protect',
            'price',
            'advert',
            'image',
            'sticker',
            'offer',
        ];
    }
}
