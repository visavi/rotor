<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Comment;
use App\Models\Feed as FeedModel;
use App\Models\Poll;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\HtmlString;

class Feed
{
    private static array $baseTypes = [
        'comments' => ['class' => Comment::class, 'withs' => ['relate', 'user', 'files']],
    ];

    private mixed $user;

    public function __construct()
    {
        $this->user = getUser();
    }

    /**
     * Get feed
     */
    private static function allTypes(): array
    {
        return array_merge(self::$baseTypes, Registry::$feeds);
    }

    public function getFeed(): HtmlString
    {
        $allTypes = self::allTypes();

        $enabledTypes = array_keys(array_filter(
            $allTypes,
            static fn ($type, $key) => setting("feed_{$key}_show"),
            ARRAY_FILTER_USE_BOTH
        ));

        $perPage = setting('feed_per_page');
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $query = FeedModel::query()
            ->whereIn('relate_type', $enabledTypes)
            ->orderByDesc('created_at');

        // Фильтруем ленту по видимым записям каждого типа до подсчёта total,
        // чтобы пагинация не считала записи, которые потом отсекаются
        foreach ($enabledTypes as $type) {
            $idQuery = $this->visibleIdsQuery($type, $allTypes[$type]);

            $query->where(function ($q) use ($type, $idQuery) {
                $q->where('relate_type', '!=', $type)
                    ->orWhereIn('relate_id', $idQuery);
            });
        }

        $version = cache()->get('feed_version', 1);
        $cacheKey = "feed_{$version}_{$currentPage}_" . implode(',', $enabledTypes);

        [$total, $items] = cache()->remember($cacheKey, (int) setting('feed_cache_time'), function () use ($query, $currentPage, $perPage, $allTypes) {
            $total = $query->count();
            $rows = $query->forPage($currentPage, $perPage)->get();
            $grouped = $rows->groupBy('relate_type');

            $loadedModels = [];
            foreach ($grouped as $type => $typeRows) {
                $class = $allTypes[$type]['class'];
                $withs = $allTypes[$type]['withs'];
                $ids = $typeRows->pluck('relate_id')->all();

                $loadedModels[$type] = $class::with($withs)->whereIn('id', $ids)->get()->keyBy('id');
            }

            $items = $rows
                ->map(fn ($row) => $loadedModels[$row->relate_type][$row->relate_id] ?? null)
                ->filter()
                ->values();

            return [$total, $items];
        });

        $posts = new LengthAwarePaginator($items, $total, $perPage, $currentPage);
        $posts->setPath(url('/'));

        $polls = $this->loadPolls($items);

        $user = $this->user;
        $allowDownload = $user || setting('down_guest_download');

        return new HtmlString((string) view('feeds/_feed', compact('posts', 'polls', 'user', 'allowDownload')));
    }

    /**
     * Подзапрос видимых id для типа: morphMap комментариев, scope модуля либо минимальный рейтинг
     */
    private function visibleIdsQuery(string $type, array $info)
    {
        /** @var class-string $class */
        $class = $info['class'];

        $idQuery = $class::query();

        if ($class === Comment::class) {
            $idQuery->whereIn('relate_type', array_keys(Relation::morphMap()));
        }

        // Scope задаёт условия видимости (активность, срок) и при необходимости join,
        // чтобы у темы стало доступно поле rating из присоединённого последнего поста
        if (! empty($info['scope'])) {
            ($info['scope'])($idQuery);
        }

        if ($minRating = setting("feed_{$type}_rating")) {
            $idQuery->where('rating', '>', $minRating);
        }

        return $idQuery->select($idQuery->getModel()->getQualifiedKeyName());
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
            $resolved = false;
            foreach (Registry::$pollResolvers as $class => $resolver) {
                if ($post instanceof $class) {
                    $result = $resolver($post);
                    if ($result) {
                        [$morphName, $id] = $result;
                        if ($id) {
                            $pairs[$morphName][] = $id;
                        }
                    }
                    $resolved = true;
                    break;
                }
            }
            if (! $resolved) {
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
