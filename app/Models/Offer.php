<?php

namespace App\Models;

class Offer extends BaseModel
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
     * Возвращает связь с голосованием
     */
    public function polling()
    {
        return $this->morphOne(Polling::class, 'relate')->where('user_id', getUser('id'));
    }

    /**
     * Возвращает связь пользователей
     */
    public function replyUser()
    {
        return $this->belongsTo(User::class, 'reply_user_id')->withDefault();
    }

    /**
     * Возвращает последнии комментарии
     *
     * @param int $limit
     * @return mixed
     */
    public function lastComments($limit = 15)
    {
        return $this->hasMany(Comment::class, 'relate_id')
            ->where('relate_type', self::class)
            ->limit($limit)
            ->with('user');
    }

    /**
     * Возвращает статус записи
     *
     * @return string
     */
    public function getStatus()
    {
        switch ($this->status) {
            case 'process':
                $status = '<i class="fa fa-spinner"></i> <b><span style="color:#0000ff">В процессе</span></b>';
                break;
            case 'done':
                $status = '<i class="fa fa-check-circle"></i> <b><span style="color:#00cc00">Выполнено</span></b>';
                break;
            case 'cancel':
                $status = '<i class="fa fa-times-circle"></i> <b><span style="color:#ff0000">Закрыто</span></b>';
                break;
            default:
                $status = '<i class="fa fa-question-circle"></i> <b><span style="color:#ffa500">Под вопросом</span></b>';
        }

        return $status;
    }

}
