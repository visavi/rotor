<?php

namespace App\Models;

class File extends BaseModel
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
     * Возвращает связанные модели
     */
    public function relate()
    {
        return $this->morphTo('relate');
    }

    /**
     * Возвращает расширение файла
     *
     * @return string
     */
    public function getExtensionAttribute()
    {
        return getExtension($this->hash);
    }

    /**
     * Возвращает является ли файл картинкой
     *
     * @return string
     */
    public function isImage()
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'gif', 'png']);
    }
}
