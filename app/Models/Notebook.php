<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\HtmlCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;

/**
 * Class Notebook
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $text
 * @property int    $created_at
 */
class Notebook extends Model
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
            'user_id' => 'int',
            'text'    => HtmlCast::class,
        ];
    }

    public function getText(): HtmlString
    {
        return renderHtml($this->text, 'notebook-' . $this->id);
    }

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }
}
