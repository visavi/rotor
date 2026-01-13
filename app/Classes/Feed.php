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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class Feed
{
    private mixed $user;

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
                $polls[Post::$morphName] = $this->getPolls($ids, Post::$morphName);
            }
        }

        if (setting('feed_news_show')) {
            $news = $this->getNews();
            $collect = $collect->merge($news);

            if ($this->user) {
                $ids = $news->pluck('id')->all();
                $polls[News::$morphName] = $this->getPolls($ids, News::$morphName);
            }
        }

        if (setting('feed_photos_show')) {
            $photos = $this->getPhotos();
            $collect = $collect->merge($photos);

            if ($this->user) {
                $ids = $photos->pluck('id')->all();
                $polls[Photo::$morphName] = $this->getPolls($ids, Photo::$morphName);
            }
        }

        if (setting('feed_articles_show')) {
            $articles = $this->getArticles();
            $collect = $collect->merge($articles);

            if ($this->user) {
                $ids = $articles->pluck('id')->all();
                $polls[Article::$morphName] = $this->getPolls($ids, Article::$morphName);
            }
        }

        if (setting('feed_downs_show')) {
            $downs = $this->getDowns();
            $collect = $collect->merge($downs);

            if ($this->user) {
                $ids = $downs->pluck('id')->all();
                $polls[Down::$morphName] = $this->getPolls($ids, Down::$morphName);
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
                $polls[Offer::$morphName] = $this->getPolls($ids, Offer::$morphName);
            }
        }

        if (setting('feed_comments_show')) {
            $comments = $this->getComments();
            $collect = $collect->merge($comments);

            if ($this->user) {
                $ids = $comments->pluck('id')->all();
                $polls[Comment::$morphName] = $this->getPolls($ids, Comment::$morphName);
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
     * Get polls
     */
    private function getPolls(array $ids, string $morphName): array
    {
        return Poll::query()
            ->whereIn('polls.relate_id', $ids)
            ->where('polls.relate_type', $morphName)
            ->where('polls.user_id', $this->user->id)
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
                ->with('lastPost.user', 'lastPost.files', 'forum.parent')
                ->orderByDesc('topics.updated_at')
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
                ->with('user', 'files')
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
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
                ->with('user', 'files')
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
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
                ->active()
                ->where('rating', '>', setting('feed_downs_rating'))
                ->with('user', 'files', 'category.parent')
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
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
                ->active()
                ->where('rating', '>', setting('feed_downs_rating'))
                ->with('user', 'files', 'category.parent')
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
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
                ->active()
                ->where('expires_at', '>', SITETIME)
                ->with('user', 'files', 'category.parent')
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
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
                ->with('user')
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
                ->get();
        });
    }

    /**
     * Get comments
     *
     * @return Collection<Comment>
     */
    public function getComments(): Collection
    {
        return Cache::remember('CommentFeed', 600, static function () {
            return Comment::query()
                ->where('rating', '>', setting('feed_comments_rating'))
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id)')
                        ->from('comments')
                        ->groupBy('relate_id');
                })
                ->with('relate', 'user')
                ->orderByDesc('created_at')
                ->limit(setting('feed_last_record'))
                ->get();
        });
    }
}
