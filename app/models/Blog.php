<?php

class Blog extends BaseModel
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
     * Возвращает последнии комментарии к статье
     *
     * @param int $limit
     * @return mixed
     */
    public function lastComments($limit = 15)
    {
        return $this->hasMany('Comment', 'relate_id')
            ->where('relate_type', self::class)
            ->limit($limit);
    }
}
