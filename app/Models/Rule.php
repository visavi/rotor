<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\HtmlCast;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Rule
 *
 * @property int             $id
 * @property string          $text
 * @property CarbonImmutable $created_at
 */
class Rule extends Model
{
    /** @use HasFactory<\Database\Factories\RuleFactory> */
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'text'       => HtmlCast::class,
            'created_at' => 'datetime',
        ];
    }
}
