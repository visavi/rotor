<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

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
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['text'];
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
    public function getText(): HtmlString
    {
        return new HtmlString(bbCode($this->text));
    }

    /**
     * Удаление записи
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            $this->polls()->delete();

            return parent::delete();
        });
    }

    /**
     * Возвращает тип связанного объекта
     */
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
