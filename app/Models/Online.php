<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Online
 *
 * @property string ip
 * @property string brow
 * @property int updated_at
 * @property int user_id
 */
class Online extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'online';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'uid';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
