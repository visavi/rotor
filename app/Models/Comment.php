<?php

namespace App\Models;

/**
 * Class Comment
 *
 * @property int id
 * @property int user_id
 * @property string relate_type
 * @property int relate_id
 * @property string text
 * @property string ip
 * @property string brow
 * @property int created_at
 */
class Comment extends BaseModel
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
}
