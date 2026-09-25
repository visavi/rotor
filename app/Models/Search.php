<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Registry;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Search
 *
 * @property int             $id
 * @property string          $relate_type
 * @property int             $relate_id
 * @property string          $text
 * @property CarbonImmutable $created_at
 */
class Search extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'search';

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
            'created_at' => 'datetime',
        ];
    }

    /**
     * Возвращает связанные объекты
     */
    public function relate(): MorphTo
    {
        return $this->morphTo('relate');
    }

    /**
     * Возвращает массив связанных объектов
     */
    public static function getRelateTypes(): array
    {
        $base = [
            Comment::$morphName => __('index.comments'),
            User::$morphName    => __('index.users'),
        ];

        foreach (array_intersect_key(Registry::$labels, Registry::$search) as $type => $label) {
            $base[$type] = __($label);
        }

        return $base;
    }

    /**
     * Возвращает тип связанного объекта
     */
    public function getRelateType(): string
    {
        $relates = self::getRelateTypes();

        return $relates[$this->relate_type] ?? __('main.undefined');
    }
}
