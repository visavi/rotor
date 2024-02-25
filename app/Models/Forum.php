<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\CategoryTreeTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Forum
 *
 * @property int id
 * @property int sort
 * @property int parent_id
 * @property string title
 * @property string description
 * @property int count_topics
 * @property int count_posts
 * @property int last_topic_id
 * @property int closed
 * @property Forum parent
 * @property Collection children
 * @property Topic lastTopic
 */
class Forum extends BaseModel
{
    use CategoryTreeTrait;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Возвращает связь родительского форума
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->withDefault();
    }

    /**
     * Возвращает связь подкатегорий форума
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    /**
     * Возвращает связь последней темы
     */
    public function lastTopic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'last_topic_id')->withDefault();
    }

    /**
     * Пересчет раздела
     */
    public function restatement(): void
    {
        $lastTopic = Topic::query()
            ->where('forum_id', $this->id)
            ->orderByDesc('updated_at')
            ->first();

        $topic = Topic::query()
            ->selectRaw('count(*) as count_topics, sum(count_posts) as count_posts')
            ->where('forum_id', $this->id)
            ->first();

        $this->update([
            'count_topics'  => (int) $topic?->count_topics,
            'count_posts'   => (int) $topic?->count_posts,
            'last_topic_id' => $lastTopic->id ?? 0,
        ]);

        if ($this->parent->id) {
            $forumIds = $this->parent->children->pluck('id')->all();
            $forumIds[] = $this->parent->id;

            $lastTopic = Topic::query()
                ->whereIn('forum_id', $forumIds)
                ->orderByDesc('updated_at')
                ->first();

            $this->parent()->update([
                'last_topic_id' => $lastTopic->id ?? 0,
            ]);
        }
    }
}
