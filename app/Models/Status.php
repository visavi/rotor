<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Status
 *
 * @property int    $id
 * @property int    $topoint
 * @property int    $point
 * @property string $name
 * @property string $color
 */
class Status extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'status';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
