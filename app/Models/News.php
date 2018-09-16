<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Class News
 *
 * @property int id
 * @property string title
 * @property string text
 * @property int user_id
 * @property string image
 * @property int created_at
 * @property int count_comments
 * @property int closed
 * @property int top
 */
class News extends BaseModel
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
    public $uploadPath = UPLOADS . '/news';

    /**
     * Записывать файлы в таблицу
     *
     * @var bool
     */
    public $dataRecord = false;

    /**
     * Возвращает комментарии новостей
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'relate');
    }

    /**
     * Возвращает иконку в зависимости от статуса
     * @return string иконка новостей
     */
    public function getIcon(): string
    {
        if ($this->closed) {
            $icon = 'fa-lock';
        } else {
            $icon = 'fa-unlock';
        }

        return $icon;
    }

    /**
     * Возвращает сокращенный текст новости
     *
     * @return string
     */
    public function shortText(): string
    {
        if (stripos($this->text, '[cut]') !== false) {
            $this->text = current(explode('[cut]', $this->text)) . ' <a href="/news/'. $this->id .'" class="badge badge-success">Читать далее &raquo;</a>';
        }

        return $this->text;
    }
}
