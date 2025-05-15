<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

/**
 * Class Offer
 *
 * @property int    $id
 * @property string $type
 * @property string $title
 * @property string $text
 * @property int    $user_id
 * @property int    $rating
 * @property int    $created_at
 * @property string $status
 * @property int    $count_comments
 * @property int    $closed
 * @property string $reply
 * @property int    $reply_user_id
 * @property int    $updated_at
 * @property-read Collection<Comment> $comments
 * @property-read Collection<Polling> $pollings
 * @property-read Polling             $polling
 * @property-read User                $replyUser
 */
class Offer extends BaseModel
{
    use SearchableTrait;

    public const DONE = 'done';
    public const WAIT = 'wait';
    public const CANCEL = 'cancel';
    public const PROCESS = 'process';

    /**
     * Статусы
     */
    public const STATUSES = [
        self::DONE,
        self::WAIT,
        self::CANCEL,
        self::PROCESS,
    ];

    public const OFFER = 'offer';
    public const ISSUE = 'issue';

    /**
     * Типы
     */
    public const TYPES = [
        self::OFFER,
        self::ISSUE,
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Morph name
     */
    public static string $morphName = 'offers';

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['title', 'text', 'reply'];
    }

    /**
     * Возвращает связь с голосованиями
     */
    public function pollings(): MorphMany
    {
        return $this->MorphMany(Polling::class, 'relate');
    }

    /**
     * Возвращает связь с голосованием
     */
    public function polling(): morphOne
    {
        return $this->morphOne(Polling::class, 'relate')
            ->where('user_id', getUser('id'));
    }

    /**
     * Возвращает связь с комментариями
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'relate')
            ->with('relate', 'user');
    }

    /**
     * Возвращает связь пользователей
     */
    public function replyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reply_user_id')->withDefault();
    }

    /**
     * Возвращает последние комментарии
     */
    public function lastComments(int $limit = 15): HasMany
    {
        return $this->hasMany(Comment::class, 'relate_id')
            ->where('relate_type', self::$morphName)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->limit($limit);
    }

    /**
     * Возвращает статус записи
     */
    public function getStatus(): HtmlString
    {
        $status = match ($this->status) {
            'process' => '<span class="fw-bold text-primary"><i class="fa fa-spinner"></i> ' . __('offers.process') . '</span>',
            'done'    => '<span class="fw-bold text-success"><i class="fa fa-check-circle"></i> ' . __('offers.done') . '</span>',
            'cancel'  => '<span class="fw-bold text-danger"><i class="fa fa-times-circle"></i> ' . __('offers.cancel') . '</span>',
            default   => '<span class="fw-bold text-warning"><i class="fa fa-question-circle"></i> ' . __('offers.wait') . '</span>',
        };

        return new HtmlString($status);
    }

    /**
     * Удаление записи
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            $this->pollings()->delete();

            $this->comments->each(static function (Comment $comment) {
                $comment->delete();
            });

            return parent::delete();
        });
    }
}
