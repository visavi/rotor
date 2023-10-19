<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class File
 *
 * @property int id
 * @property string relate_type
 * @property int relate_id
 * @property string hash
 * @property string name
 * @property int size
 * @property int user_id
 * @property int created_at
 * @property string extension
 * @property BaseModel relate
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
     * Возвращает связанные объекты
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
     * Является ли файл картинкой
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return in_array($this->extension, explode(',', setting('image_extensions')), true);
    }

    /**
     * Является ли файл аудио
     *
     * @return bool
     */
    public function isAudio(): bool
    {
        return $this->extension === 'mp3';
    }

    /**
     * Удаление записи и загруженных файлов
     *
     * @return bool|null
     */
    public function delete(): ?bool
    {
        deleteFile(public_path($this->hash));

        return parent::delete();
    }
}
