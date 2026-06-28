<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\HtmlCast;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Notice
 *
 * @property int             $id
 * @property string          $type
 * @property string          $name
 * @property string          $text
 * @property int             $user_id
 * @property int $protect
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
class Notice extends Model
{
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
            'user_id' => 'int',
            'text'    => HtmlCast::class,
        ];
    }

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }
}
