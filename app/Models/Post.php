<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\UploadTrait;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Class Post
 *
 * @property int id
 * @property int topic_id
 * @property int user_id
 * @property string text
 * @property int rating
 * @property int created_at
 * @property string ip
 * @property string brow
 * @property int edit_user_id
 * @property int updated_at
 * @property Collection files
 */
class Post extends BaseModel
{
    use UploadTrait;

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
     * Директория загрузки файлов
     *
     * @var string
     */
    public $uploadPath = '/uploads/forums';

    /**
     * Morph name
     *
     * @var string
     */
    public static $morphName = 'posts';

    /**
     * Возвращает связь пользователей
     *
     * @return BelongsTo
     */
    public function editUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edit_user_id')->withDefault();
    }

    /**
     * Возвращает топик
     *
     * @return BelongsTo
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id')->withDefault();
    }

    /**
     * Возвращает загруженные файлы
     *
     * @return MorphMany
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate');
    }

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
     * Удаление поста и загруженных файлов
     *
     * @return bool|null
     * @throws Exception
     */
    public function delete(): ?bool
    {
        $this->files->each(static function (File $file) {
            $file->delete();
        });

        return parent::delete();
    }
}
