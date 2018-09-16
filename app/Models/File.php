<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class File
 *
 * @property int id
 */
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
     *
     * @return MorphTo
     */
    public function relate(): MorphTo
    {
        return $this->morphTo('relate');
    }

    /**
     * Возвращает расширение файла
     *
     * @return string
     */
    public function getExtensionAttribute(): string
    {
        return getExtension($this->hash);
    }

    /**
     * Возвращает является ли файл картинкой
     *
     * @return string
     */
    public function isImage(): string
    {
        return \in_array($this->extension, ['jpg', 'jpeg', 'gif', 'png']);
    }
}
