<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SearchableTrait;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Class Comment
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $relate_type
 * @property int    $relate_id
 * @property string $text
 * @property string $ip
 * @property string $brow
 * @property int    $created_at
 * @property-read Collection<Poll> $polls
 * @property-read Poll             $poll
 */
class Comment extends Model
{
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
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'int',
        ];
    }

    /**
     * Morph name
     */
    public static string $morphName = 'comments';

    /**
     * Директория загрузки файлов
     */
    public string $uploadPath = '/uploads/comments';

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['text'];
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate')
            ->orderBy('created_at');
    }

    /**
     * Возвращает файлы
     */
    public function getFiles(): Collection
    {
        return $this->files->filter(static fn (File $f) => ! $f->isImage());
    }

    /**
     * Возвращает картинки
     */
    public function getImages(): Collection
    {
        return $this->files->filter(static fn (File $f) => $f->isImage());
    }

    /**
     * Возвращает картинки, не вставленные в текст
     */
    public function getDetachedImages(): Collection
    {
        return $this->getImages()->reject(fn (File $f) => str_contains($this->text ?? '', $f->path));
    }

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Возвращает связанные объекты
     */
    public function relate(): MorphTo
    {
        return $this->morphTo('relate');
    }

    /**
     * Возвращает связь с голосованиями
     */
    public function polls(): MorphMany
    {
        return $this->MorphMany(Poll::class, 'relate');
    }

    /**
     * Возвращает связь с голосованием пользователя
     */
    public function poll(): morphOne
    {
        return $this->morphOne(Poll::class, 'relate')
            ->where('user_id', getUser('id'));
    }

    /**
     * Get text
     */
    public function getText(bool $withImages = true): HtmlString
    {
        $text = $withImages
            ? $this->text
            : preg_replace('/<img[^>]*>/', '', $this->text);

        return renderHtml($text, 'comment-' . $this->id);
    }

    /**
     * Удаление записи
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            $this->polls()->delete();

            $this->files->each(static function (File $file) {
                $file->delete();
            });

            return parent::delete();
        });
    }

    /**
     * Возвращает тип связанного объекта
     */
    public function getViewUrl(bool $absolute = true): string
    {
        $plural = Str::plural($this->relate_type);
        $params = $this->relate_type === Article::$morphName
            ? ['slug' => $this->relate->slug, 'cid' => $this->id]
            : ['id' => $this->relate_id, 'cid' => $this->id];

        return route($plural . '.view', $params, $absolute);
    }

    public function getRelateType(): string
    {
        return match ($this->relate_type) {
            Article::$morphName => __('index.blogs'),
            Down::$morphName    => __('index.loads'),
            News::$morphName    => __('index.news'),
            Offer::$morphName   => __('index.offers'),
            Photo::$morphName   => __('index.photos'),
        };
    }
}
