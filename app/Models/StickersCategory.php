<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StickersCategory
 *
 * @property int             $id
 * @property string          $name
 * @property CarbonImmutable $updated_at
 * @property CarbonImmutable $created_at
 */
class StickersCategory extends Model
{
    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
}
