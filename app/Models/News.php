<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\HtmlString;

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
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Директория загрузки файлов
     */
    public string $uploadPath = '/uploads/news';

    /**
     * Morph name
     */
    public static string $morphName = 'news';

    /**
     * Возвращает комментарии новостей
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'relate')->with('relate');
    }

    /**
     * Возвращает связь с голосованием
     */
    public function polling(): morphOne
    {
        return $this->morphOne(Polling::class, 'relate')->where('user_id', getUser('id'));
    }

    /**
     * Возвращает иконку в зависимости от статуса
     *
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
     */
    public function shortText(): HtmlString
    {
        $more = null;

        if (str_contains($this->text, '[cut]')) {
            $this->text = current(explode('[cut]', $this->text));
            $more = view('app/_more', ['link' => '/news/' . $this->id]);
        }

        return new HtmlString(bbCode($this->text) . $more);
    }
}
