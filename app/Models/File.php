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
 * @property object relate
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
     * @return bool
     */
    public function isImage(): bool
    {
        return \in_array($this->extension, ['jpg', 'jpeg', 'gif', 'png'], true);
    }

    /**
     * Скачивает файл
     *
     * @return void
     */
    public function download(): void
    {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $this->name . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $this->size);
        readfile(HOME . $this->hash);
        exit;
    }
}
