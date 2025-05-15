<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\AddFileToArchiveTrait;
use App\Traits\ConvertVideoTrait;
use App\Traits\SearchableTrait;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;

/**
 * Class Post
 *
 * @property int    $id
 * @property int    $topic_id
 * @property int    $user_id
 * @property string $text
 * @property int    $rating
 * @property int    $created_at
 * @property string $ip
 * @property string $brow
 * @property int    $edit_user_id
 * @property int    $updated_at
 * @property-read Collection<File>    $files
 * @property-read Collection<Polling> $pollings
 * @property-read Polling             $polling
 * @property-read Topic               $topic
 * @property-read User                $editUser
 */
class Post extends BaseModel
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
    public string $uploadPath = '/uploads/forums';

    /**
     * Morph name
     */
    public static string $morphName = 'posts';

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['text'];
    }

    /**
     * Возвращает связь пользователей
     */
    public function editUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edit_user_id')->withDefault();
    }

    /**
     * Возвращает топик
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id')->withDefault();
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
    public function pollings(): MorphMany
    {
        return $this->morphMany(Polling::class, 'relate');
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
     * Удаление поста и загруженных файлов
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            $this->pollings()->delete();

            $this->files->each(static function (File $file) {
                $file->delete();
            });

            return parent::delete();
        });
    }
}
