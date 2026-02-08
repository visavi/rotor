<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    public const INPUT = 'input';
    public const TEXTAREA = 'textarea';

    /**
     * All types
     */
    public const TYPES = [
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
     * Возвращает данные
     */
    public function data(): HasMany
    {
        return $this->hasMany(UserData::class, 'field_id');
    }
}
