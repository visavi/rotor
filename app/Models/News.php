<?php

namespace App\Models;

class News extends BaseModel
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
     * Возвращает иконку в зависимости от статуса
     * @return string иконка новостей
     */
    public function getIcon()
    {
        if ($this->closed) {
            $icon = 'fa-lock';
        } else {
            $icon = 'fa-unlock';
        }

        return $icon;
    }

    /**
     * Возвращает сокращенный текст новости
     *
     * @return string
     */
    public function shortText()
    {
        if (stristr($this->text, '[cut]')) {
            $this->text = current(explode('[cut]', $this->text)) . ' <a href="/news/'. $this->id .'" class="badge badge-success">Читать далее &raquo;</a>';
        }

        return $this->text;
    }
}
