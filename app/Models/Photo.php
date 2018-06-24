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
    public $uploadPath = 'photos';

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
     * Загружает файл
     *
     * @param UploadedFile $file
     * @return void
     */
    public function uploadFile(UploadedFile $file)
    {
        $upload = uploadFile($file, UPLOADS . '/photos');

        File::query()->create([
            'relate_id'   => $this->id,
            'relate_type' => self::class,
            'hash'        => $upload['filename'],
            'name'        => $upload['name'],
            'size'        => $upload['filesize'],
            'user_id'     => getUser('id'),
            'created_at'  => SITETIME,
        ]);
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
            deleteFile(UPLOADS . '/photos/' . $file->hash);
            $file->delete();
        });

        return parent::delete();
    }
}
