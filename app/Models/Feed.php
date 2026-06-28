<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Feed
 *
 * @property int             $id
 * @property string          $relate_type
 * @property int             $relate_id
 * @property CarbonImmutable $created_at
 */
class Feed extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
