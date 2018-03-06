<?php

namespace App\Models;

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
     */
    public function parent()
    {
        return $this->belongsTo(Forum::class, 'parent_id');
    }

    /**
     * Возвращает связь подкатегорий форума
     */
    public function children()
    {
        return $this->hasMany(Forum::class, 'parent_id')->orderBy('sort');
    }

    /**
     * Возвращает связь последней темы
     */
    public function lastTopic()
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
            ->selectRaw('count(*) as topics, sum(posts) as posts')
            ->where('forum_id', $this->id)
            ->first();

        $this->update([
            'count_topics'  => $lastTopic ? (int) $topic->count_topics : 0,
            'count_posts'   => $lastTopic ? (int) $topic->count_posts : 0,
            'last_topic_id' => $lastTopic ? $lastTopic->id : 0,
        ]);

        if ($this->parent) {
            $this->parent()->update([
                'last_topic_id' => $lastTopic ? $lastTopic->id : 0
            ]);
        }
    }
}
