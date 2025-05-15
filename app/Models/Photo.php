<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ConvertVideoTrait;
use App\Traits\SearchableTrait;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Class Photo
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $title
 * @property string $text
 * @property int    $created_at
 * @property int    $rating
 * @property int    $closed
 * @property int    $count_comments
 * @property-read Collection<File> $files
 */
class Photo extends BaseModel
{
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
    public string $uploadPath = '/uploads/photos';

    /**
     * Morph name
     */
    public static string $morphName = 'photos';

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['title', 'text'];
    }

    /**
     * Возвращает комментарии фотографий
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'relate')->with('relate');
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate');
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
     * Удаление фото и загруженных файлов
     */
    public function delete(): ?bool
    {
        $this->comments->each(static function (Comment $comment) {
            $comment->delete();
        });

        $this->files->each(static function (File $file) {
            $file->delete();
        });

        return parent::delete();
    }
}
