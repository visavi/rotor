<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\HtmlString;

/**
 * Class Topic
 *
 * @property int id
 * @property int forum_id
 * @property string title
 * @property int user_id
 * @property int closed
 * @property int locked
 * @property int count_posts
 * @property int visits
 * @property int updated_at
 * @property string|null moderators
 * @property string note
 * @property int last_post_id
 * @property int close_user_id
 * @property int created_at
 * @property Forum forum
 * @property Collection posts
 * @property Vote vote
 */
class Topic extends BaseModel
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
     * Counting field
     *
     * @var string
     */
    public $countingField = 'visits';

    /**
     * Morph name
     *
     * @var string
     */
    public static $morphName = 'topics';

    /**
     * Возвращает сообщения
     *
     * @return HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'topic_id');
    }

    /**
     * Возвращает закладки
     *
     * @return HasMany
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class, 'topic_id');
    }

    /**
     * Возвращает голосование
     *
     * @return hasOne
     */
    public function vote(): hasOne
    {
        return $this->hasOne(Vote::class, 'topic_id')->withDefault();
    }

    /**
     * Возвращает последнее сообщение
     *
     * @return BelongsTo
     */
    public function lastPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'last_post_id')->withDefault();
    }

    /**
     * Возвращает раздел форума
     *
     * @return BelongsTo
     */
    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class, 'forum_id')->withDefault();
    }

    /**
     * Возвращает связь пользователей
     *
     * @return BelongsTo
     */
    public function closeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'close_user_id')->withDefault();
    }

    /**
     * Возвращает иконку в зависимости от статуса
     *
     * @return string иконка топика
     */
    public function getIcon(): string
    {
        if ($this->closed) {
            $icon = 'fa-lock';
        } elseif ($this->locked) {
            $icon = 'fa-thumbtack';
        } else {
            $icon = 'fa-folder-open';
        }

        return $icon;
    }

    /**
     * Генерирует постраничную навигация для форума
     *
     * @param  string $url
     * @return HtmlString|null сформированный блок
     */
    public function pagination(string $url = '/topics'): ?HtmlString
    {
        if (! $this->count_posts) {
            return null;
        }

        $pages = [];
        $link = $url . '/' . $this->id;

        $pg_cnt = ceil($this->count_posts / setting('forumpost'));

        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $pg_cnt) {
                $pages[] = [
                    'page' => $i,
                    'title' => $i.' страница',
                    'name' => $i,
                ];
            }
        }

        if (5 < $pg_cnt) {
            if (6 < $pg_cnt) {
                $pages[] = [
                    'separator' => true,
                    'name' => ' ... ',
                ];
            }

            $pages[] = [
                'page' => $pg_cnt,
                'title' => $pg_cnt.' страница',
                'name' => $pg_cnt,
            ];
        }

        return new HtmlString(view('forums/_pagination', compact('pages', 'link')));
    }

    /**
     * Пересчет темы
     *
     * @return void
     */
    public function restatement(): void
    {
        $lastPost = Post::query()
            ->where('topic_id', $this->id)
            ->orderByDesc('updated_at')
            ->first();

        $countPosts = Post::query()->where('topic_id', $this->id)->count();

        $this->update([
            'count_posts'  => $countPosts,
            'last_post_id' => $lastPost->id ?? 0,
        ]);

        $this->forum->restatement();
    }

    /**
     * Get count posts
     *
     * @return HtmlString
     */
    public function getCountPosts(): HtmlString
    {
        $newPosts = null;
        if (isset($this->bookmark_posts) && $this->count_posts > $this->bookmark_posts) {
            $newPosts = ' <span style="color:#00aa00">+' . ($this->count_posts - $this->bookmark_posts) . '</span>';
        }


        return new HtmlString($this->count_posts . $newPosts);
    }
}
