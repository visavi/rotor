<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Dialogue
 *
 * @property int    $id
 * @property int    $message_id
 * @property int    $user_id
 * @property int    $author_id
 * @property string $type
 * @property int    $reading
 * @property int    $created_at
 * @property-read User $author
 * @property-read Message $message
 */
class Dialogue extends BaseModel
{
    public const IN = 'in';   // Принятые
    public const OUT = 'out';  // Отправленные

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
     *
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withDefault();
    }

    /**
     * Message
     *
     * @return BelongsTo
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id')->withDefault();
    }

    /**
     * Удаление собщений диалога
     *
     * @return bool|null
     */
    public function delete(): ?bool
    {
        // Если сообщение осталось только у одного пользователя
        if ($this->message->dialogues->count() === 1) {
            $this->message->delete();
        }

        return parent::delete();
    }
}
