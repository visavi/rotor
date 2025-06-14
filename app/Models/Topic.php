<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SearchableTrait;
use App\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

/**
 * Class Topic
 *
 * @property int         $id
 * @property int         $forum_id
 * @property string      $title
 * @property int         $user_id
 * @property int         $closed
 * @property int         $locked
 * @property int         $count_posts
 * @property int         $visits
 * @property int         $updated_at
 * @property int         $created_at
 * @property string|null $moderators
 * @property string      $note
 * @property int         $last_post_id
 * @property int         $close_user_id
 * @property-read Forum                $forum
 * @property-read Collection<Post>     $posts
 * @property-read Collection<Bookmark> $bookmarks
 * @property-read Vote                 $vote
 */
class Topic extends BaseModel
{
    use SearchableTrait;
    use SortableTrait;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * Counting field
     */
    public string $countingField = 'visits';

    /**
     * Morph name
     */
    public static string $morphName = 'topics';

    /**
     * Возвращает поля участвующие в поиске
     */
    public function searchableFields(): array
    {
        return ['title'];
    }

    /**
     * Возвращает список сортируемых полей
     */
    protected static function sortableFields(): array
    {
        return [
            'date'   => ['field' => 'updated_at', 'label' => __('main.date')],
            'visits' => ['field' => 'visits', 'label' => __('main.views')],
            'posts'  => ['field' => 'count_posts', 'label' => __('main.messages')],
        ];
    }

    /**
     * Возвращает сообщения
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'topic_id');
    }

    /**
     * Возвращает закладки
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class, 'topic_id');
    }

    /**
     * Возвращает голосование
     */
    public function vote(): hasOne
    {
        return $this->hasOne(Vote::class, 'topic_id')->withDefault();
    }

    /**
     * Возвращает последнее сообщение
     */
    public function lastPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'last_post_id')->withDefault();
    }

    /**
     * Возвращает раздел форума
     */
    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class, 'forum_id')->withDefault();
    }

    /**
     * Возвращает связь пользователей
     */
    public function closeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'close_user_id')->withDefault();
    }

    /**
     * Удаление темы и связанных данных
     */
    public function delete(): ?bool
    {
        return DB::transaction(function () {
            // Удаление голосования
            $this->vote->delete();

            // Удаление закладок
            $this->bookmarks->each(static function (Bookmark $bookmark) {
                $bookmark->delete();
            });

            // Удаление сообщений
            $this->posts->each(static function (Post $post) {
                $post->delete();
            });

            return parent::delete();
        });
    }

    /**
     * Возвращает иконку в зависимости от статуса
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
     * Генерирует постраничную навигацию для форума
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
                    'page'  => $i,
                    'title' => $i . ' страница',
                    'name'  => $i,
                    'url'   => $i > 1 ? $link . '?page=' . $i : $link,
                ];
            }
        }

        if ($pg_cnt > 5) {
            if ($pg_cnt > 6) {
                $pages[] = [
                    'separator' => true,
                    'name'      => ' ... ',
                ];
            }

            $pages[] = [
                'page'  => $pg_cnt,
                'title' => $pg_cnt . ' страница',
                'name'  => $pg_cnt,
                'url'   => $link . '?page=' . $pg_cnt,
            ];
        }

        return new HtmlString(view('forums/_pagination', compact('pages')));
    }

    /**
     * Пересчет темы
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
     */
    public function getCountPosts(): HtmlString
    {
        $newPosts = null;
        if ($this->bookmark_posts && $this->count_posts > $this->bookmark_posts) {
            $newPosts = ' <span style="color:#00aa00">+' . ($this->count_posts - $this->bookmark_posts) . '</span>';
        }

        return new HtmlString($this->count_posts . $newPosts);
    }
}
