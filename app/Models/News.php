<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\AddFileToArchiveTrait;
use App\Traits\ConvertVideoTrait;
use App\Traits\SearchableTrait;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

/**
 * Class News
 *
 * @property int    $id
 * @property string $title
 * @property string $text
 * @property int    $user_id
 * @property int    $created_at
 * @property int    $count_comments
 * @property int    $closed
 * @property int    $top
 * @property-read Collection<Comment> $comments
 * @property-read Collection<File>    $files
 * @property-read Collection<Poll>    $polls
 * @property-read Poll                $poll
 */
class News extends BaseModel
{
    use AddFileToArchiveTrait;
    use ConvertVideoTrait;
    use SearchableTrait;
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
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['title', 'text'];
    }

    /**
     * Возвращает комментарии новостей
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'relate')->with('relate');
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Возвращает файлы
     */
    public function getFiles(): Collection
    {
        return $this->files->filter(static function (File $value, $key) {
            return ! $value->isImage();
        });
    }

    /**
     * Возвращает картинки
     */
    public function getImages(): Collection
    {
        return $this->files->filter(static function (File $value, $key) {
            return $value->isImage();
        });
    }

    /**
     * Возвращает связь с голосованиями
     */
    public function polls(): MorphMany
    {
        return $this->MorphMany(Poll::class, 'relate');
    }

    /**
     * Возвращает связь с голосованием
     */
    public function poll(): morphOne
    {
        return $this->morphOne(Poll::class, 'relate')
            ->where('user_id', getUser('id'));
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

    /**
     * Удаление новость и загруженных файлов
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            $this->polls()->delete();

            $this->comments->each(static function (Comment $comment) {
                $comment->delete();
            });

            $this->files->each(static function (File $file) {
                $file->delete();
            });

            return parent::delete();
        });
    }
}
