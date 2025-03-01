<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Article;
use App\Models\Down;
use App\Models\Item;
use App\Models\News;
use App\Models\Offer;
use App\Models\Photo;
use App\Models\Polling;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class Feed
{
    /**
     * @var User|mixed
     */
    private $user;

    public function __construct()
    {
        $this->user = getUser();
    }

    /**
     * Get feed
     */
    public function getFeed(): HtmlString
    {
        $polls = [];
        $collect = new Collection();

        if (setting('feed_topics_show')) {
            $topics = $this->getTopics();
            $collect = $collect->merge($topics);

            if ($this->user) {
                $ids = $topics->pluck('last_post_id')->all();
                $polls[Post::$morphName] = $this->getPolling($ids, Post::$morphName);
            }
        }

        if (setting('feed_news_show')) {
            $news = $this->getNews();
            $collect = $collect->merge($news);

            if ($this->user) {
                $ids = $news->pluck('id')->all();
                $polls[News::$morphName] = $this->getPolling($ids, News::$morphName);
            }
        }

        if (setting('feed_photos_show')) {
            $photos = $this->getPhotos();
            $collect = $collect->merge($photos);

            if ($this->user) {
                $ids = $photos->pluck('id')->all();
                $polls[Photo::$morphName] = $this->getPolling($ids, Photo::$morphName);
            }
        }

        if (setting('feed_articles_show')) {
            $articles = $this->getArticles();
            $collect = $collect->merge($articles);

            if ($this->user) {
                $ids = $articles->pluck('id')->all();
                $polls[Article::$morphName] = $this->getPolling($ids, Article::$morphName);
            }
        }

        if (setting('feed_downs_show')) {
            $downs = $this->getDowns();
            $collect = $collect->merge($downs);

            if ($this->user) {
                $ids = $downs->pluck('id')->all();
                $polls[Down::$morphName] = $this->getPolling($ids, Down::$morphName);
            }
        }

        if (setting('feed_items_show')) {
            $collect = $collect->merge($this->getItems());
        }

        if (setting('feed_offers_show')) {
            $offers = $this->getOffers();
            $collect = $collect->merge($offers);

            if ($this->user) {
                $ids = $offers->pluck('id')->all();
                $polls[Offer::$morphName] = $this->getPolling($ids, Offer::$morphName);
            }
        }

        $posts = $collect
            ->sortByDesc('created_at')
            ->sortByDesc('top')
            ->take(setting('feed_total'));

        $user = $this->user;
        $posts = simplePaginate($posts, setting('feed_per_page'));
        $allowDownload = $user || setting('down_guest_download');

        return new HtmlString(view('feeds/_feed', compact('posts', 'polls', 'user', 'allowDownload')));
    }

    /**
     * Get polling
     */
    private function getPolling(array $ids, string $morphName): array
    {
        return Polling::query()
            ->whereIn('pollings.relate_id', $ids)
            ->where('pollings.relate_type', $morphName)
            ->where('pollings.user_id', $this->user->id)
            ->pluck('vote', 'relate_id')
            ->all();
    }

    /**
     * Get topics
     *
     * @return Collection<Topic>
     */
    public function getTopics(): Collection
    {
        return Cache::remember('TopicFeed', 600, static function () {
            return Topic::query()
                ->select('topics.*', 'posts.created_at')
                ->join('posts', function ($join) {
                    $join->on('last_post_id', 'posts.id')
                        ->where('posts.rating', '>', setting('feed_topics_rating'));
                })
                ->orderByDesc('topics.updated_at')
                ->with('lastPost.user', 'lastPost.files', 'forum.parent')
                ->limit(setting('feed_last_record'))
                ->get();
        });
    }

    /**
     * Get news
     *
     * @return Collection<News>
     */
    public function getNews(): Collection
    {
        return Cache::remember('NewsFeed', 600, static function () {
            return News::query()
                ->where('rating', '>', setting('feed_news_rating'))
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
                ->with('user')
                ->get();
        });
    }

    /**
     * Get photos
     *
     * @return Collection<Photo>
     */
    public function getPhotos(): Collection
    {
        return Cache::remember('PhotoFeed', 600, static function () {
            return Photo::query()
                ->where('rating', '>', setting('feed_photos_rating'))
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
                ->with('user', 'files')
                ->get();
        });
    }

    /**
     * Get articles
     *
     * @return Collection<Article>
     */
    public function getArticles(): Collection
    {
        return Cache::remember('ArticleFeed', 600, static function () {
            return Article::query()
                ->where('rating', '>', setting('feed_downs_rating'))
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
                ->with('user', 'files', 'category.parent')
                ->get();
        });
    }

    /**
     * Get downs
     *
     * @return Collection<Down>
     */
    public function getDowns(): Collection
    {
        return Cache::remember('DownFeed', 600, static function () {
            return Down::query()
                ->where('active', 1)
                ->where('rating', '>', setting('feed_downs_rating'))
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
                ->with('user', 'files', 'category.parent')
                ->get();
        });
    }

    /**
     * Get items
     *
     * @return Collection<Item>
     */
    public function getItems(): Collection
    {
        return Cache::remember('ItemFeed', 600, static function () {
            return Item::query()
                ->where('expires_at', '>', SITETIME)
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
                ->with('user', 'files', 'category.parent')
                ->get();
        });
    }

    /**
     * Get offers
     *
     * @return Collection<Offer>
     */
    public function getOffers(): Collection
    {
        return Cache::remember('OfferFeed', 600, static function () {
            return Offer::query()
                ->where('rating', '>', setting('feed_offers_rating'))
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
                ->with('user')
                ->get();
        });
    }
}
