<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\HtmlCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;

/**
 * Class Chat
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $text
 * @property string $ip
 * @property string $brow
 * @property int    $created_at
 * @property int    $edit_user_id
 * @property int    $updated_at
 */
class Chat extends Model
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
        return renderHtml($this->text, 'chat-' . $this->id);
    }

    /**
     * Возвращает связь пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Возвращает связь пользователей
     */
    public function editUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edit_user_id')->withDefault();
    }
}
