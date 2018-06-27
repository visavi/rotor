<?php

namespace App\Models;

use Illuminate\Http\UploadedFile;

class Photo extends BaseModel
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
     * Директория загрузки файлов
     *
     * @var string
     */
    public $uploadPath = UPLOADS . '/photos';

    /**
     * Возвращает комментарии фотографий
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'relate');
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files()
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Удаление фото и загруженных файлов
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {
        $this->files->each(function($file) {
            deleteFile(HOME . $file->hash);
            $file->delete();
        });

        return parent::delete();
    }
}
