<?php

namespace App\Models;

use Illuminate\Http\UploadedFile;

class Down extends BaseModel
{
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
     * Список расширений доступных для просмотра в архиве
     *
     * @var array
     */
    public static $viewExt = ['xml', 'wml', 'asp', 'aspx', 'shtml', 'htm', 'phtml', 'html', 'php', 'htt', 'dat', 'tpl', 'htaccess', 'pl', 'js', 'jsp', 'css', 'txt', 'sql', 'gif', 'png', 'bmp', 'wbmp', 'jpg', 'jpeg', 'env', 'gitignore', 'json', 'yml', 'md'];

    /**
     * Возвращает категорию загрузок
     */
    public function category()
    {
        return $this->belongsTo(Load::class, 'category_id')->withDefault();
    }

    /**
     * Возвращает последнии комментарии к файлу
     *
     * @param int $limit
     * @return mixed
     */
    public function lastComments($limit = 15)
    {
        return $this->hasMany(Comment::class, 'relate_id')
            ->where('relate_type', self::class)
            ->limit($limit);
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files()
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Возвращает файлы
     */
    public function getFiles()
    {
        return $this->files->filter(function ($value, $key) {
            return ! $value->isImage();
        });
    }

    /**
     * Возвращает картинки
     */
    public function getImages()
    {
        return $this->files->filter(function ($value, $key) {
            return $value->isImage();
        });
    }

    /**
     * Обрезает текст
     *
     * @return string
     */
    public function cutText()
    {
        if (utfStrlen($this->text) > 300) {
            $this->text = strip_tags(bbCode($this->text), '<br>');
            $this->text = str_limit($this->text, 300);
        }

        return $this->text;
    }

    /**
     * Возвращает массив доступных расширений для просмотра в архиве
     *
     * @return array
     */
    public static function getViewExt()
    {
        return self::$viewExt;
    }

    /**
     * Загружает файл
     *
     * @param UploadedFile $file
     * @return void
     */
    public function uploadFile(UploadedFile $file)
    {
        $path = in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'gif', 'png']) ? 'screen' : 'files';
        $fileName = uploadFile($file, UPLOADS . '/' . $path . '/');

        File::query()->create([
            'relate_id'   => $this->id,
            'relate_type' => self::class,
            'hash'        => $fileName,
            'name'        => $file->getClientOriginalName(),
            'size'        => $file->getClientSize(),
            'user_id'     => getUser('id'),
            'created_at'  => SITETIME,
        ]);
    }
}
