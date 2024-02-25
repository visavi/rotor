<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Class Message
 *
 * @property int    $id
 * @property int    $user_id
 * @property int    $author_id
 * @property string $text
 * @property int    $created_at
 * @property-read User $author
 * @property-read Collection<File> $files
 * @property-read Collection<Dialogue> $dialogues
 */
class Message extends BaseModel
{
    use UploadTrait;

    public const IN = 'in'; // Принятые
    public const OUT = 'out'; // Отправленные

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
    public static string $morphName = 'messages';

    /**
     * Директория загрузки файлов
     */
    public string $uploadPath = '/uploads/messages';

    /**
     * Возвращает связь пользователей
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withDefault();
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     */
    public function dialogues(): HasMany
    {
        return $this->hasMany(Dialogue::class);
    }

    /**
     * Create dialogue
     *
     *
     * @return Builder|Model
     */
    public function createDialogue(User $user, ?User $author, string $text, bool $withAuthor)
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

        if ($authorId && $withAuthor) {
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

        return $message;
    }

    /**
     * Удаление сообщения и загруженных файлов
     */
    public function delete(): ?bool
    {
        $this->files->each(static function (File $file) {
            $file->delete();
        });

        return parent::delete();
    }
}
