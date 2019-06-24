<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\UploadTrait;
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
    public $uploadPath = UPLOADS . '/news';

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
            $this->text = bbCode(current(explode('[cut]', $this->text)));
            $this->text .= '<div class="mt-1"><a href="/news/'. $this->id .'" class="btn btn-sm btn-info">Читать дальше &raquo;</a></div>';
        }

        return $this->text;
    }
}
