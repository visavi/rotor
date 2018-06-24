<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public $uploadPath;

    /**
     * Возвращает связь пользователей
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Возвращает логин пользователя
     *
     * @param string $value
     * @return string
     */
    public function getLoginAttribute($value): string
    {
        return $value ?? setting('guestsuser');
    }

    /**
     * Возвращает директорию загрузки файлов
     *
     * @return string
     */
    public function getUploadPath(): string
    {
        return UPLOADS . '/' . $this->uploadPath;
    }
}
