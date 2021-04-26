<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Inbox
 *
 * @property int id
 * @property int user_id
 * @property int author_id
 * @property string text
 * @property int created_at
 */
class Message extends BaseModel
{
    public const IN   = 'in';   // Принятые
    public const OUT  = 'out';  // Отправленные

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
     * Morph name
     *
     * @var string
     */
    public static $morphName = 'messages';

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
     * Create dialogue
     *
     * @param User      $user
     * @param User|null $author
     * @param string    $text
     */
    public function createDialogue(User $user, ?User $author, string $text): void
    {
        $authorId = $author->id ?? 0;

        $message = self::query()->create([
            'user_id'    => $user->id,
            'author_id'  => $authorId,
            'text'       => $text,
            'created_at' => SITETIME,
        ]);

        Dialogue::query()->create([
            'message_id' => $message->id,
            'user_id'    => $user->id,
            'author_id'  => $authorId,
            'type'       => self::IN,
            'created_at' => SITETIME,
        ]);

        if ($authorId) {
            Dialogue::query()->create([
                'message_id' => $message->id,
                'user_id'    => $authorId,
                'author_id'  => $user->id,
                'type'       => self::OUT,
                'reading'    => 1,
                'created_at' => SITETIME,
            ]);
        }

        $user->increment('newprivat');
    }
}
