<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class ChangeMail
 *
 * @property int id
 * @property int user_id
 * @property string mail
 * @property string hash
 * @property int created_at
 */
class ChangeMail extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'changemail';

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
