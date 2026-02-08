<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Antimat
 *
 * @property int    $id
 * @property string $string
 */
class Antimat extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'antimat';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Очищает строку от мата по базе слов
     */
    public static function replace(string $str): string
    {
        $words = self::query()
            ->orderByDesc(DB::raw('CHAR_LENGTH(string)'))
            ->pluck('string')
            ->all();

        if ($words) {
            foreach ($words as $word) {
                $str = preg_replace('/' . preg_quote($word, '/') . '/iu', '***', $str);
            }
        }

        return $str;
    }
}
