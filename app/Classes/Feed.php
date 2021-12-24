<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Article;
use App\Models\Down;
use App\Models\Item;
use App\Models\News;
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
     *
     * @param int $show
     *
     * @return HtmlString
     */
    public function getFeed(int $show = 20): HtmlString
    {
        $topicsEnable = true;
        $newsEnable = true;
        $photosEnable = true;
        $articlesEnable = true;
        $downsEnable = true;
        $itemsEnable = true;

        $polls = [];
        $collect = new Collection();


        if ($topicsEnable) {
            $topics = $this->getTopics();
            $collect = $collect->merge($topics);

            if ($this->user) {
                $polls[Post::$morphName] = $this->getPolling($topics->pluck('last_post_id')->all(), Post::$morphName);
            }
        }

        if ($newsEnable) {
            $news = $this->getNews();
            $collect = $collect->merge($news);

            if ($this->user) {
                $polls[News::$morphName] = $this->getPolling($news->pluck('id')->all(), News::$morphName);
            }
        }

        if ($photosEnable) {
            $photos = $this->getPhotos();
            $collect = $collect->merge($photos);

            if ($this->user) {
                $polls[Photo::$morphName] = $this->getPolling($photos->pluck('id')->all(), Photo::$morphName);
            }
        }

        if ($articlesEnable) {
            $articles = $this->getArticles();
            $collect = $collect->merge($articles);

            if ($this->user) {
                $polls[Article::$morphName] = $this->getPolling($articles->pluck('id')->all(), Article::$morphName);
            }
        }

        if ($downsEnable) {
            $downs = $this->getDowns();
            $collect = $collect->merge($downs);

            if ($this->user) {
                $polls[Down::$morphName] = $this->getPolling($downs->pluck('id')->all(), Down::$morphName);
            }
        }

        if ($itemsEnable) {
            $collect = $collect->merge($this->getItems());
        }

        $posts = $collect
                ->sortByDesc('created_at')
                ->take(100);

        $user  = $this->user;
        $posts = simplePaginate($posts, $show);
        $allowDownload = $user || setting('down_guest_download');

        return new HtmlString(view('widgets/_feed', compact('posts', 'polls', 'user', 'allowDownload')));
    }

    /**
     * Get polling
     *
     * @param array  $ids
     * @param string $morphName
     *
     * @return array
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
                        ->where('posts.rating', '>', -3);
                })
                ->orderByDesc('topics.updated_at')
                ->with('lastPost.user', 'lastPost.files', 'forum.parent')
                ->limit(20)
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
                ->where('rating', '>', -3)
                ->orderByDesc('created_at')
                ->limit(20)
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
                ->where('rating', '>', -3)
                ->orderByDesc('created_at')
                ->limit(20)
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
                ->orderByDesc('created_at')
                ->limit(20)
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
                ->where('rating', '>', -3)
                ->orderByDesc('created_at')
                ->limit(20)
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
                ->limit(20)
                ->with('user', 'files', 'category.parent')
                ->get();
        });
    }
}
