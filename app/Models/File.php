<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

/**
 * Class File
 *
 * @property int    $id
 * @property string $relate_type
 * @property int    $relate_id
 * @property string $path
 * @property string $name
 * @property int    $size
 * @property string $extension
 * @property string $mime_type
 * @property int    $user_id
 * @property int    $created_at
 * @property-read BaseModel $relate
 */
class File extends BaseModel
{
    public const VIDEO_EXTENSIONS = ['mp4', 'webm'];
    public const AUDIO_EXTENSIONS = ['mp3', 'wav', 'ogg'];
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];

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
     * Возвращает url файла
     */
    public function getUrl(): string
    {
        return asset($this->path);
    }

    /**
     * Является ли файл картинкой
     */
    public function isImage(): bool
    {
        return in_array($this->extension, self::IMAGE_EXTENSIONS, true);
    }

    /**
     * Является ли файл аудио
     */
    public function isAudio(): bool
    {
        return in_array($this->extension, self::AUDIO_EXTENSIONS, true);
    }

    /**
     * Является ли файл видео
     */
    public function isVideo(): bool
    {
        return in_array($this->extension, self::VIDEO_EXTENSIONS, true);
    }

    /**
     * Удаление записи и загруженных файлов
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            deleteFile(public_path($this->path));

            return parent::delete();
        });
    }
}
