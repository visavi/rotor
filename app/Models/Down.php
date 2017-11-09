<?php

namespace App\Models;

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
        return $this->belongsTo(Cats::class, 'category_id')->withDefault();
    }

    /**
     * Возвращает директорию категории
     *
     * @return string
     */
    public function getFolderAttribute()
    {
        return $this->category->folder ? $this->category->folder.'/' : '';
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
}
