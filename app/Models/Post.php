<?php

namespace App\Models;

use Illuminate\Http\UploadedFile;

class Post extends BaseModel
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
    public $uploadPath = UPLOADS . '/forums';

    /**
     * Возвращает связь пользователей
     */
    public function editUser()
    {
        return $this->belongsTo(User::class, 'edit_user_id')->withDefault();
    }

    /**
     * Возвращает топик
     */
    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id')->withDefault();
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files()
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Загружает файл
     *
     * @param  UploadedFile $file
     * @param  string       $uploadPath
     * @return string
     */
    public function uploadFile(UploadedFile $file, $uploadPath = null): string
    {
        if (! file_exists($this->uploadPath . '/' . $this->topic->id)) {
            $old = umask(0);
            mkdir($this->uploadPath . '/' . $this->topic->id, 0777, true);
            umask($old);
        }

        return parent::uploadFile($file, $this->uploadPath . '/' . $this->topic->id);
    }

    /**
     * Удаление поста и загруженных файлов
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {
        $this->files->each(function($file) {
            deleteFile($this->uploadPath . '/' . $this->topic_id . '/' . $file->hash);
            $file->delete();
        });

        return parent::delete();
    }
}
