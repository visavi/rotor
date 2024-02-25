<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Counter24
 *
 * @property int id
 * @property string period
 * @property int hosts
 * @property int hits
 */
class Counter24 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'counters24';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
