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
     * @param UploadedFile $file
     * @return void
     */
    public function uploadFile(UploadedFile $file)
    {
        if (! file_exists(UPLOADS . '/forum/' . $this->topic->id)) {
            $old = umask(0);
            mkdir(UPLOADS . '/forum/' . $this->topic->id, 0777, true);
            umask($old);
        }

        $fileName = uploadFile($file, UPLOADS . '/forum/' . $this->topic->id);

        File::query()->create([
            'relate_id'   => $this->id,
            'relate_type' => self::class,
            'hash'        => $fileName,
            'name'        => $file->getClientOriginalName(),
            'size'        => $file->getClientSize(),
            'user_id'     => getUser('id'),
            'created_at'  => SITETIME,
        ]);
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
            deleteFile(UPLOADS . '/forum/' . $this->topic_id . '/' . $file->hash);
            $file->delete();
        });

        return parent::delete();
    }
}
