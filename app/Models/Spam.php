<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Spam
 *
 * @property int             $id
 * @property string          $relate_type
 * @property int             $relate_id
 * @property int             $user_id
 * @property string          $path
 * @property CarbonImmutable $created_at
 */
class Spam extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'spam';

    /**
     * The name of the "updated at" column.
     */
    public const ?string UPDATED_AT = null;

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
        ];
    }

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Возвращает связанные объекты
     */
    public function relate(): MorphTo
    {
        return $this->morphTo('relate');
    }

    /**
     * Возвращает объект пользователя
     */
    public function getRelateUser(): ?User
    {
        if (! $this->relate) {
            return null;
        }

        if ($this->relate->getAttribute('user_id') || $this->relate->getAttribute('author_id')) {
            $user = $this->relate->getRelationValue('author') ?? $this->relate->getRelationValue('user');

            return $user instanceof User ? $user : null;
        }

        return null;
    }
}
