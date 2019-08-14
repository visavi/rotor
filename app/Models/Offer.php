<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Class Offer
 *
 * @property int id
 * @property string type
 * @property string title
 * @property string text
 * @property int user_id
 * @property int rating
 * @property int created_at
 * @property string status
 * @property int count_comments
 * @property int closed
 * @property string reply
 * @property int reply_user_id
 * @property int updated_at
 */
class Offer extends BaseModel
{
    public const DONE    = 'done';
    public const WAIT    = 'wait';
    public const CANCEL  = 'cancel';
    public const PROCESS = 'process';

    /**
     * Статусы
     */
    public const STATUSES = [
        self::DONE    => 'Выполнено',
        self::WAIT    => 'Под вопросом',
        self::CANCEL  => 'Закрыто',
        self::PROCESS => 'В процессе',
    ];

    public const OFFER = 'offer';
    public const ISSUE = 'issue';

    /**
     * Типы
     */
    public const TYPES = [
        self::OFFER => 'Предложения',
        self::ISSUE => 'Проблемы',
    ];

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
     * Возвращает связь с голосованием
     *
     * @return morphOne
     */
    public function polling(): morphOne
    {
        return $this->morphOne(Polling::class, 'relate')->where('user_id', getUser('id'));
    }

    /**
     * Возвращает связь пользователей
     *
     * @return BelongsTo
     */
    public function replyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reply_user_id')->withDefault();
    }

    /**
     * Возвращает последнии комментарии
     *
     * @param int $limit
     * @return HasMany
     */
    public function lastComments($limit = 15): HasMany
    {
        return $this->hasMany(Comment::class, 'relate_id')
            ->where('relate_type', self::class)
            ->orderBy('created_at')
            ->with('user')
            ->limit($limit);
    }

    /**
     * Возвращает статус записи
     *
     * @return string
     */
    public function getStatus(): string
    {
        $statuses = self::$statuses;

        switch ($this->status) {
            case 'process':
                $status = '<i class="fa fa-spinner"></i> <b><span style="color:#0000ff">' . $statuses['process'] . '</span></b>';
                break;
            case 'done':
                $status = '<i class="fa fa-check-circle"></i> <b><span style="color:#00cc00">' . $statuses['done'] . '</span></b>';
                break;
            case 'cancel':
                $status = '<i class="fa fa-times-circle"></i> <b><span style="color:#ff0000">' . $statuses['cancel'] . '</span></b>';
                break;
            default:
                $status = '<i class="fa fa-question-circle"></i> <b><span style="color:#ffa500">' . $statuses['wait'] . '</span></b>';
        }

        return $status;
    }

}
