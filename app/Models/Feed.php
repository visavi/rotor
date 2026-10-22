<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Feed
 *
 * @property int             $id
 * @property string          $relate_type
 * @property int             $relate_id
 * @property bool            $pinned
 * @property CarbonImmutable $created_at
 */
class Feed extends Model
{
    /** @use HasFactory<\Database\Factories\FeedFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'pinned'     => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
