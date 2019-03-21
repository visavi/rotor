<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Error
 *
 * @property int id
 * @property int code
 * @property string request
 * @property string referer
 * @property int user_id
 * @property string ip
 * @property string brow
 * @property int created_at
 */
class Error extends BaseModel
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
