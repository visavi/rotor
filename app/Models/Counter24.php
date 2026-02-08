<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Counter24
 *
 * @property int    $id
 * @property string $period
 * @property int    $hosts
 * @property int    $hits
 */
class Counter24 extends Model
{
    /**
     * The table associated with the model.
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
