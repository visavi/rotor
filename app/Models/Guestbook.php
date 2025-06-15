<?php

namespace App\Models;

use App\Traits\ConvertVideoTrait;
use App\Traits\SearchableTrait;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

/**
 * Class Guestbook
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $text
 * @property string $ip
 * @property string $brow
 * @property int    $created_at
 * @property string $reply
 * @property int    $edit_user_id
 * @property bool   $active
 * @property int    $updated_at
 * @property-read Collection<File> $files
 */
class Guestbook extends BaseModel
{
    use ConvertVideoTrait;
    use SearchableTrait;
    use UploadTrait;

    /**
     * The table associated with the model.
     */
    protected $table = 'guestbook';

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
    public static string $morphName = 'guestbook';

    /**
     * Директория загрузки файлов
     */
    public string $uploadPath = '/uploads/guestbook';

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['text', 'reply'];
    }

    /**
     * Scope a query to only include active records.
     */
    #[Scope]
    protected function active(Builder $query, bool $type = true): void
    {
        $query->where('active', $type);
    }

    /**
     * Возвращает связь пользователей
     */
    public function editUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edit_user_id')->withDefault();
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
     * Удаление записи и загруженных файлов
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
