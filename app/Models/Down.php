<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ConvertVideoTrait;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\HtmlString;
use ZipArchive;

/**
 * Class Down
 *
 * @property int id
 * @property int category_id
 * @property string title
 * @property string text
 * @property int user_id
 * @property int created_at
 * @property int count_comments
 * @property int rating
 * @property int loads
 * @property int active
 * @property array links
 * @property int updated_at
 * @property Collection files
 * @property Collection comments
 * @property Load category
 */
class Down extends BaseModel
{
    use ConvertVideoTrait;
    use UploadTrait;

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'links' => 'array',
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
     * Директория загрузки файлов
     */
    public string $uploadPath = '/uploads/files';

    /**
     * Counting field
     */
    public string $countingField = 'loads';

    /**
     * Список расширений доступных для просмотра в архиве
     */
    public array $viewExt = ['xml', 'wml', 'asp', 'aspx', 'shtml', 'htm', 'phtml', 'html', 'php', 'htt', 'dat', 'tpl', 'htaccess', 'pl', 'js', 'jsp', 'css', 'txt', 'sql', 'gif', 'png', 'bmp', 'wbmp', 'jpg', 'jpeg', 'webp', 'env', 'gitignore', 'json', 'yml', 'md'];

    /**
     * Morph name
     */
    public static string $morphName = 'downs';

    /**
     * Возвращает категорию загрузок
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Load::class, 'category_id')->withDefault();
    }

    /**
     * Возвращает комментарии
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'relate')->with('relate');
    }

    /**
     * Возвращает связь с голосованием
     */
    public function polling(): morphOne
    {
        return $this->morphOne(Polling::class, 'relate')->where('user_id', getUser('id'));
    }

    /**
     * Возвращает последнии комментарии к файлу
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
     * Возвращает сокращенный текст описания
     */
    public function shortText(int $words = 50): HtmlString
    {
        if (wordCount($this->text) > $words) {
            $this->text = bbCodeTruncate($this->text, $words);
        } else {
            $this->text = bbCode($this->text);
        }

        return new HtmlString($this->text);
    }

    /**
     * Возвращает массив доступных расширений для просмотра в архиве
     */
    public function getViewExt(): array
    {
        return $this->viewExt;
    }

    /**
     * Загружает файл
     */
    public function uploadAndConvertFile(UploadedFile $file): array
    {
        $uploadFile = $this->uploadFile($file);
        $this->convertVideo($uploadFile);
        $this->addFileToArchive($uploadFile);

        return $uploadFile;
    }

    /**
     * Удаление загрузки и загруженных файлов
     */
    public function delete(): ?bool
    {
        $this->files->each(static function (File $file) {
            $file->delete();
        });

        return parent::delete();
    }

    /**
     * Add file to archive
     */
    private function addFileToArchive(array $file): void
    {
        if (
            $file['extension'] === 'zip'
            && setting('archive_file_path')
            && ! str_contains(setting('archive_file_path'), '..')
            && file_exists(public_path(setting('archive_file_path')))
        ) {
            $archive = new ZipArchive();
            $opened = $archive->open(public_path($file['path']));

            if ($opened === true) {
                $archive->addFile(public_path(setting('archive_file_path')), basename(setting('archive_file_path')));
                $archive->close();
            }
        }
    }
}
