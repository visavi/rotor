<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Comment;
use App\Models\Feed as FeedModel;
use App\Models\Poll;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Throwable;

class Feed
{
    private mixed $user;

    public function __construct()
    {
        $this->user = getUser();
    }

    /**
     * Встроенные типы плюс зарегистрированные модулями
     */
    private static function allTypes(): array
    {
        return array_merge([
            'comments' => [
                'class' => Comment::class,
                'with'  => ['relate', 'user', 'files'],
                'scope' => fn ($query) => $query->visible(),
            ],
        ], Registry::$feeds);
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
        $currentPage = Paginator::resolveCurrentPage();

        $query = FeedModel::query()
            ->whereIn('relate_type', $enabledTypes)
            ->orderByDesc('created_at');

        // Отсекаем невидимые записи каждого типа прямо в запросе,
        // чтобы они не попадали в выборку и не ломали пагинацию
        foreach ($enabledTypes as $type) {
            $idQuery = $this->visibleIdsQuery($type, $allTypes[$type]);

            $query->where(function ($q) use ($type, $idQuery) {
                $q->where('relate_type', '!=', $type)
                    ->orWhereIn('relate_id', $idQuery);
            });
        }

        $version = cache()->get('feed_version', 1);
        $cacheKey = "feed_{$version}_{$currentPage}_" . implode(',', $enabledTypes);

        $items = cache()->remember($cacheKey, (int) setting('feed_cache_time'), function () use ($query, $currentPage, $perPage, $allTypes) {
            // Берём на одну запись больше, чтобы Paginator определил наличие следующей страницы без отдельного count()
            $rows = $query->skip(($currentPage - 1) * $perPage)->take($perPage + 1)->get();
            $grouped = $rows->groupBy('relate_type');

            $loadedModels = [];
            foreach ($grouped as $type => $typeRows) {
                $class = $allTypes[$type]['class'];
                $with = $allTypes[$type]['with'];
                $ids = $typeRows->pluck('relate_id')->all();

                $loadedModels[$type] = $class::with($with)->whereIn('id', $ids)->get()->keyBy('id');
            }

            return $rows
                ->map(fn ($row) => $loadedModels[$row->relate_type][$row->relate_id] ?? null)
                ->filter()
                ->values();
        });

        $posts = new Paginator($this->render($items), $perPage, $currentPage);
        $posts->setPath(url('/'));

        return new HtmlString((string) view('feeds/_feed', compact('posts')));
    }

    /**
     * Рендер элементов ленты с изоляцией
     */
    private function render(Collection $items): Collection
    {
        $polls = $this->loadPolls($items);
        $allowDownload = $this->user || setting('down_guest_download');

        return $items
            ->map(function ($post) use ($polls, $allowDownload) {
                $view = Registry::$feeds[$post->getMorphClass()]['view'] ?? 'feeds._' . $post->getMorphClass();

                try {
                    return view($view, [
                        'post'          => $post,
                        'polls'         => $polls,
                        'user'          => $this->user,
                        'allowDownload' => $allowDownload,
                    ])->render();
                } catch (Throwable $e) {
                    report($e);

                    return null;
                }
            })
            ->filter()
            ->values();
    }

    /**
     * Подзапрос видимых id для типа: scope типа либо минимальный рейтинг
     */
    private function visibleIdsQuery(string $type, array $info)
    {
        /** @var class-string $class */
        $class = $info['class'];

        $idQuery = $class::query();

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
            $morphName = $post->getMorphClass();

            // Резолвер указывает, что голосование привязано к связанной записи (тема -> последний пост)
            if ($resolver = Registry::$feeds[$morphName]['poll'] ?? null) {
                if ($result = $resolver($post)) {
                    [$type, $id] = $result;
                    if ($id) {
                        $pairs[$type][] = $id;
                    }
                }
                continue;
            }

            $pairs[$morphName][] = $post->id;
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
