<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Login
 *
 * @property int id
 * @property int user_id
 * @property string ip
 * @property string brow
 * @property int created_at
 * @property int type
 */
class Login extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'login';

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
