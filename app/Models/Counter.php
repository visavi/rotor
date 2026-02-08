<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Counter
 *
 * @property int    $id
 * @property string $period
 * @property int    $allhosts
 * @property int    $allhits
 * @property int    $dayhosts
 * @property int    $dayhits
 * @property int    $hosts24
 * @property int    $hits24
 */
class Counter extends Model
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
