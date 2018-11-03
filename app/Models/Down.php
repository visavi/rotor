<?php

namespace App\Models;

use App\Traits\UploadTrait;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;

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
 * @property int rated
 * @property int loads
 * @property int active
 * @property int updated_at
 * @property Collection files
 * @property Collection comments
 * @property Load category
 */
class Down extends BaseModel
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
    public $uploadPath = UPLOADS . '/files';

    /**
     * Список расширений доступных для просмотра в архиве
     *
     * @var array
     */
    public static $viewExt = ['xml', 'wml', 'asp', 'aspx', 'shtml', 'htm', 'phtml', 'html', 'php', 'htt', 'dat', 'tpl', 'htaccess', 'pl', 'js', 'jsp', 'css', 'txt', 'sql', 'gif', 'png', 'bmp', 'wbmp', 'jpg', 'jpeg', 'env', 'gitignore', 'json', 'yml', 'md'];

    /**
     * Возвращает категорию загрузок
     *
     * @return BelongsTo
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
        return $this->morphMany(Comment::class, 'relate');
    }

    /**
     * Возвращает последнии комментарии к файлу
     *
     * @param int $limit
     * @return HasMany
     */
    public function lastComments($limit = 15): HasMany
    {
        return $this->hasMany(Comment::class, 'relate_id')
            ->where('relate_type', self::class)
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
     *
     * @return Collection
     */
    public function getFiles(): Collection
    {
        return $this->files->filter(function (File $value, $key) {
            return ! $value->isImage();
        });
    }

    /**
     * Возвращает картинки
     *
     * @return Collection
     */
    public function getImages(): Collection
    {
        return $this->files->filter(function (File $value, $key) {
            return $value->isImage();
        });
    }

    /**
     * Обрезает текст
     *
     * @param int $limit
     * @return string
     */
    public function cutText($limit = 200): string
    {
        if (\strlen($this->text) > $limit) {
            $this->text = strip_tags(bbCode($this->text), '<br>');
            $this->text = mb_substr($this->text, 0, mb_strrpos(mb_substr($this->text, 0, $limit), ' ')) . '...';
        }

        return $this->text;
    }

    /**
     * Возвращает массив доступных расширений для просмотра в архиве
     *
     * @return array
     */
    public static function getViewExt(): array
    {
        return self::$viewExt;
    }

    /**
     * Загружает файл
     *
     * @param  UploadedFile $file
     * @return array
     */
    public function uploadFile(UploadedFile $file): array
    {
        $upload = parent::uploadFile($file);
        $this->convertVideo($file, $upload['path']);

        return $upload;
    }

    /**
     * @param UploadedFile $file
     * @param int          $path
     */
    public function convertVideo(UploadedFile  $file, $path): void
    {
        $isVideo = strpos($file->getClientMimeType(), 'video/') !== false;

        // Обработка видео
        if ($isVideo && env('FFMPEG_ENABLED')) {

            $ffconfig = [
                'ffmpeg.binaries'  => env('FFMPEG_PATH'),
                'ffprobe.binaries' => env('FFPROBE_PATH'),
                'timeout'          => env('FFMPEG_TIMEOUT'),
                'ffmpeg.threads'   => env('FFMPEG_THREADS'),
            ];

            $ffmpeg = FFMpeg::create($ffconfig);

            $video = $ffmpeg->open(HOME . $path);

            // Сохраняем скрин с 5 секунды
            $frame = $video->frame(TimeCode::fromSeconds(5));
            $frame->save(HOME . $path . '.jpg');

            File::query()->create([
                'relate_id'   => $this->id,
                'relate_type' => self::class,
                'hash'        => $path . '.jpg',
                'name'        => 'screenshot.jpg',
                'size'        => filesize(HOME . $path . '.jpg'),
                'user_id'     => getUser('id'),
                'created_at'  => SITETIME,
            ]);

            // Перекодируем видео в h264
            $ffprobe = FFProbe::create($ffconfig);
            $video = $ffprobe
                ->streams(HOME . $path)
                ->videos()
                ->first();

            if ($video && $video->get('codec_name') !== 'h264' && $file->getClientOriginalExtension() === 'mp4') {
                $format = new X264('libmp3lame', 'libx264');
                $video->save($format, HOME . $path . '.convert');

                rename(HOME . $path . '.convert', HOME . $path);
            }
        }
    }

    /**
     * Удаление загрузки и загруженных файлов
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete(): ?bool
    {
        $this->files->each(function($file) {

            deleteFile(HOME . $file->hash);
            $file->delete();
        });

        return parent::delete();
    }
}
