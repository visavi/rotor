<?php

declare(strict_types=1);

namespace App\Models;

use App\Classes\HtmlSanitizer;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * Class UserField
 *
 * @property int    $id
 * @property int    $sort
 * @property string $type
 * @property string $name
 * @property int    $min
 * @property int    $max
 * @property bool   $required
 * @property-read Collection<UserData> $data
 */
class UserField extends Model
{
    /**
     * Type fields
     */
    public const string INPUT = 'input';
    public const string TEXTAREA = 'textarea';

    /**
     * All types
     */
    public const array TYPES = [
        self::INPUT,
        self::TEXTAREA,
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'sort',
        'type',
        'name',
        'min',
        'max',
        'required',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'required' => 'bool',
        ];
    }

    /**
     * Scope для получения полей с данными пользователя
     */
    #[Scope]
    protected function withUserData(Builder $query, int $userId): void
    {
        $query->select('user_fields.*', 'user_data.value')
            ->leftJoin('user_data', static function (JoinClause $join) use ($userId) {
                $join->on('user_fields.id', 'user_data.field_id')
                    ->where('user_data.user_id', $userId);
            })
            ->orderBy('user_fields.sort');
    }

    /**
     * Санитайзит значение в зависимости от типа поля
     */
    public function sanitizeValue(?string $value): ?string
    {
        if ($this->type === self::TEXTAREA) {
            return HtmlSanitizer::sanitize($value);
        }

        return $value;
    }

    /**
     * Возвращает данные
     */
    public function data(): HasMany
    {
        return $this->hasMany(UserData::class, 'field_id');
    }
}
