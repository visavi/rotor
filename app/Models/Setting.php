<?php

namespace App\Models;

class Setting extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'setting';

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
     * Возвращает список допустимых страниц
     *
     * @return array
     */
    public static function getActions()
    {
        return [
            'main',
            'mail',
        ];
    }
}
