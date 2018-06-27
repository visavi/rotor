<?php

namespace App\Models;

use Illuminate\Http\UploadedFile;

class Item extends BaseModel
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
     * Директория загрузки файлов
     *
     * @var string
     */
    public $uploadPath = UPLOADS . '/boards';

    /**
     * Возвращает категорию объявлений
     */
    public function category()
    {
        return $this->belongsTo(Board::class, 'board_id')->withDefault();
    }

    /**
     * Возвращает загруженные файлы
     */
    public function files()
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Возвращает путь к первому файлу
     *
     * @return mixed имя файла
     */
    public function getFirstImage()
    {
        $image = $this->files->first();

        $path = $image ? $image->hash : null;
        return resizeImage($path, ['alt' => $this->title, 'class' => 'img-fluid']);
    }

    /**
     * Обрезает текст
     *
     * @param int $limit
     * @return string
     */
    public function cutText($limit = 200)
    {
        if (strlen($this->text) > $limit) {
            $this->text = strip_tags(bbCode($this->text), '<br>');
            $this->text = mb_substr($this->text, 0, mb_strrpos(mb_substr($this->text, 0, $limit), ' ')) . '...';
        }

        return $this->text;
    }
}
