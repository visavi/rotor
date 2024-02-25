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
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Возвращает связанные объекты
     */
    public function relate(): MorphTo
    {
        return $this->morphTo('relate');
    }

    /**
     * Возвращает расширение файла
     */
    public function getExtensionAttribute(): string
    {
        return getExtension($this->hash);
    }

    /**
     * Является ли файл картинкой
     */
    public function isImage(): bool
    {
        return in_array($this->extension, explode(',', setting('image_extensions')), true);
    }

    /**
     * Является ли файл аудио
     */
    public function isAudio(): bool
    {
        return $this->extension === 'mp3';
    }

    /**
     * Удаление записи и загруженных файлов
     */
    public function delete(): ?bool
    {
        deleteFile(public_path($this->hash));

        return parent::delete();
    }
}
