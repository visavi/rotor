<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\HtmlCast;
use App\Traits\ConvertVideoTrait;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

/**
 * Class Message
 *
 * @property int    $id
 * @property int    $user_id
 * @property int    $author_id
 * @property string $text
 * @property int    $created_at
 * @property-read User $user
 * @property-read User $author
 * @property-read Collection<File> $files
 * @property-read Collection<Dialogue> $dialogues
 */
class Message extends Model
{
    use ConvertVideoTrait;
    use UploadTrait;

    public const string IN = 'in'; // Принятые
    public const string OUT = 'out'; // Отправленные

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
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'int',
            'text'    => HtmlCast::class,
        ];
    }

    /**
     * Get text
     */
    public function getText(): HtmlString
    {
        return renderHtml($this->text, 'message-' . $this->id);
    }

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Возвращает связь владельца
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
        return $this->morphMany(File::class, 'relate')
            ->orderBy('created_at');
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
     * Возвращает картинки, не вставленные в текст
     */
    public function getDetachedImages(): Collection
    {
        return $this->getImages()->reject(fn (File $f) => str_contains($this->text ?? '', $f->path));
    }

    /**
     * Возвращает связь с диалогами
     */
    public function dialogues(): HasMany
    {
        return $this->hasMany(Dialogue::class);
    }

    /**
     * Create dialogue
     */
    public function createDialogue(User $user, ?User $author, string $text, bool $withAuthor): self
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
        return DB::transaction(function () {
            $this->files->each(static function (File $file) {
                $file->delete();
            });

            return parent::delete();
        });
    }
}
