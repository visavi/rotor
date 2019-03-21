<?php

declare(strict_types=1);

namespace App\Models;

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
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Возвращает связь родительского форума
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->withDefault();
    }

    /**
     * Возвращает связь подкатегорий форума
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    /**
     * Возвращает связь последней темы
     *
     * @return BelongsTo
     */
    public function lastTopic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'last_topic_id')->withDefault();
    }

    /**
     * Пересчет раздела
     *
     * @return void
     */
    public function restatement(): void
    {
        $lastTopic = Topic::query()
            ->where('forum_id', $this->id)
            ->orderBy('updated_at', 'desc')
            ->first();

        $topic = Topic::query()
            ->selectRaw('count(*) as count_topics, sum(count_posts) as count_posts')
            ->where('forum_id', $this->id)
            ->first();

        $this->update([
            'count_topics'  => $topic ? (int) $topic->count_topics : 0,
            'count_posts'   => $topic ? (int) $topic->count_posts : 0,
            'last_topic_id' => $lastTopic ? $lastTopic->id : 0,
        ]);

        if ($this->parent->id) {

            $forumIds = $this->parent->children->pluck('id')->all();
            $forumIds[] = $this->parent->id;

            $lastTopic = Topic::query()
                ->whereIn('forum_id', $forumIds)
                ->orderBy('updated_at', 'desc')
                ->first();

            $this->parent()->update([
                'last_topic_id' => $lastTopic ? $lastTopic->id : 0
            ]);
        }
    }
}
