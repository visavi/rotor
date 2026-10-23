<?php

declare(strict_types=1);

namespace App\Models;

use App\Attributes\NotFoundMessage;
use App\Casts\HtmlCast;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property int             $protect
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
#[NotFoundMessage('admin.notices.notice_not_found')]
class Notice extends Model
{
    /** @use HasFactory<\Database\Factories\NoticeFactory> */
    use HasFactory;

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
