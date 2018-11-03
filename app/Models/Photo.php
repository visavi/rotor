<?php

namespace App\Models;

use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Class Photo
 *
 * @property int id
 * @property int user_id
 * @property string title
 * @property string text
 * @property int created_at
 * @property int rating
 * @property int closed
 * @property int count_comments
 */
class Photo extends BaseModel
{
    use UploadTrait;

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
     * Директория загрузки файлов
     *
     * @var string
     */
    public $uploadPath = UPLOADS . '/photos';

    /**
     * Возвращает комментарии фотографий
     *
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'relate');
    }

    /**
     * Возвращает загруженные файлы
     *
     * @return MorphMany
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Удаление фото и загруженных файлов
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete(): ?bool
    {
        $this->files->each(function($file) {
            deleteFile(HOME . $file->hash);
            $file->delete();
        });

        return parent::delete();
    }
}
