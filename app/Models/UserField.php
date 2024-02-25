<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class User
 *
 * @property int id
 * @property int sort
 * @property string type
 * @property string name
 * @property int min
 * @property int max
 * @property bool required
 * @property Collection<UserData> data
 */
class UserField extends BaseModel
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
     * The attributes that should be cast to native types.
     *
     * @var string[]
     */
    protected $casts = [
        'required' => 'bool',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
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
     * Возвращает данные
     */
    public function data(): HasMany
    {
        return $this->hasMany(UserData::class, 'field_id');
    }
}
