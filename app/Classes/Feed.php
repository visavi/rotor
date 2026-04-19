<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Down;
use App\Models\Item;
use App\Models\News;
use App\Models\Offer;
use App\Models\Photo;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Topic;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class Feed
{
    private mixed $user;

    private array $modelMap = [
        'topics'   => Topic::class,
        'news'     => News::class,
        'photos'   => Photo::class,
        'articles' => Article::class,
        'downs'    => Down::class,
        'items'    => Item::class,
        'offers'   => Offer::class,
        'comments' => Comment::class,
    ];

    private array $withsMap = [
        'topics'   => ['lastPost.user', 'lastPost.files', 'forum.parent'],
        'news'     => ['user', 'files'],
        'photos'   => ['user', 'files'],
        'articles' => ['user', 'files', 'category.parent'],
        'downs'    => ['user', 'files', 'category.parent'],
        'items'    => ['user', 'files', 'category.parent'],
        'offers'   => ['user'],
        'comments' => ['relate', 'user'],
    ];

    private array $settingMap = [
        'topics'   => 'feed_topics_show',
        'news'     => 'feed_news_show',
        'photos'   => 'feed_photos_show',
        'articles' => 'feed_articles_show',
        'downs'    => 'feed_downs_show',
        'items'    => 'feed_items_show',
        'offers'   => 'feed_offers_show',
        'comments' => 'feed_comments_show',
    ];

    private array $ratingMap = [
        'topics'   => 'feed_topics_rating',
        'news'     => 'feed_news_rating',
        'photos'   => 'feed_photos_rating',
        'articles' => 'feed_articles_rating',
        'downs'    => 'feed_downs_rating',
        'items'    => 'feed_items_rating',
        'offers'   => 'feed_offers_rating',
        'comments' => 'feed_comments_rating',
    ];

    public function __construct()
    {
        $this->user = getUser();
    }

    /**
     * Get feed
     */
    public function getFeed(): HtmlString
    {
        $enabledTypes = array_keys(array_filter(
            $this->settingMap,
            static fn ($value) => setting($value)
        ));

        $perPage = setting('feed_per_page');
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $query = DB::table('feeds')
            ->whereIn('relate_type', $enabledTypes)
            ->orderByDesc('created_at');

        $version = cache()->get('feed_version', 1);
        $cacheKey = "feed_{$version}_{$currentPage}_" . implode(',', $enabledTypes);

        [$total, $items] = cache()->remember($cacheKey, (int) setting('feed_cache_time'), function () use ($query, $currentPage, $perPage) {
            $total = $query->count();
            $rows = $query->forPage($currentPage, $perPage)->get();
            $grouped = $rows->groupBy('relate_type');

            $loadedModels = [];
            foreach ($grouped as $type => $typeRows) {
                $class = $this->modelMap[$type];
                $withs = $this->withsMap[$type];
                $ids = $typeRows->pluck('relate_id')->all();

                $modelQuery = $class::with($withs)->whereIn('id', $ids);

                if ($type === 'items') {
                    $modelQuery->where('expires_at', '>', SITETIME);
                }

                $minRating = setting($this->ratingMap[$type]);
                if ($minRating) {
                    if ($type === 'topics') {
                        $modelQuery->whereHas('lastPost', fn ($q) => $q->where('rating', '>', $minRating));
                    } else {
                        $modelQuery->where('rating', '>', $minRating);
                    }
                }

                $loadedModels[$type] = $modelQuery->get()->keyBy('id');
            }

            $items = $rows
                ->map(fn ($row) => $loadedModels[$row->relate_type][$row->relate_id] ?? null)
                ->filter()
                ->values();

            return [$total, $items];
        });

        $posts = new LengthAwarePaginator($items, $total, $perPage, $currentPage);
        $posts->setPath(request()->url());

        $polls = $this->loadPolls($items);

        $user = $this->user;
        $allowDownload = $user || setting('down_guest_download');

        return new HtmlString((string) view('feeds/_feed', compact('posts', 'polls', 'user', 'allowDownload')));
    }

    /**
     * Load polls
     */
    private function loadPolls($posts): array
    {
        if (! $this->user) {
            return [];
        }

        $pairs = [];

        foreach ($posts as $post) {
            if ($post instanceof Topic) {
                if ($post->last_post_id) {
                    $pairs[Post::$morphName][] = $post->last_post_id;
                }
            } else {
                $pairs[$post->getMorphClass()][] = $post->id;
            }
        }

        if (empty($pairs)) {
            return [];
        }

        $query = Poll::query()
            ->where('user_id', $this->user->id)
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as $morphName => $ids) {
                    $q->orWhere(fn ($inner) => $inner->where('relate_type', $morphName)->whereIn('relate_id', $ids));
                }
            });

        $polls = [];
        foreach ($query->get(['relate_type', 'relate_id', 'vote']) as $poll) {
            $polls[$poll->relate_type][$poll->relate_id] = $poll->vote;
        }

        return $polls;
    }
}
