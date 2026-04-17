<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\HtmlCast;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Rule
 *
 * @property int    $id
 * @property string $text
 * @property int    $created_at
 */
class Rule extends Model
{
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
            'text' => HtmlCast::class,
        ];
    }
}
