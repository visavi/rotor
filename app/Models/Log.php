<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Log
 *
 * @property int id
 * @property int user_id
 * @property string request
 * @property string referer
 * @property string ip
 * @property string brow
 * @property int created_at
 */
class Log extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
