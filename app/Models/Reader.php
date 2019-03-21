<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Reader
 *
 * @property int id
 * @property string relate_type
 * @property int relate_id
 * @property string ip
 * @property int created_at
 */
class Reader extends BaseModel
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
