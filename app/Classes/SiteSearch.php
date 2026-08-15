<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Comment;
use App\Models\Search;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Полнотекстовый поиск по сайту, общий для страницы поиска и API
 */
class SiteSearch
{
    public const MIN_LENGTH = 3;

    public const MAX_LENGTH = 64;

    public const SORTS = ['relevance', 'date', 'date_asc'];

    /**
     * Разделы, участвующие в поиске
     */
    public static function types(): array
    {
        return Search::getRelateTypes();
    }

    /**
     * Очищает запрос от пунктуации, ломающей fulltext
     */
    public static function clean(string $query): string
    {
        return trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $query));
    }

    /**
     * Готовит поисковый запрос: убирает слова короче минимальной длины
     */
    public static function terms(string $query): string
    {
        $words = array_filter(
            explode(' ', self::clean($query)),
            static fn (string $word) => mb_strlen($word) >= self::MIN_LENGTH,
        );

        return implode(' ', $words);
    }

    /**
     * Приводит сортировку к допустимой
     */
    public static function sort(?string $sort): string
    {
        return in_array($sort, self::SORTS, true) ? $sort : 'relevance';
    }

    /**
     * Приводит раздел к допустимому, неизвестный раздел означает поиск везде
     */
    public static function type(?string $type): ?string
    {
        return isset(self::types()[$type]) ? $type : null;
    }

    /**
     * Ищет записи по подготовленному запросу
     */
    public static function paginate(string $terms, ?string $type, string $sort, int $perPage): LengthAwarePaginator
    {
        $order = match ($sort) {
            'date'     => ['created_at desc'],
            'date_asc' => ['created_at asc'],
            default    => ['match(text) against(? in boolean mode) desc', [$terms . '*']],
        };

        $posts = Search::query()
            ->whereIn('relate_type', array_keys(self::types()))
            ->when($type, static function ($query) use ($type) {
                $query->where('relate_type', $type);
            })
            // Удаленные и скрытые комментарии в выдаче не нужны
            ->where(static function ($query) {
                $query->where('relate_type', '!=', Comment::$morphName)
                    ->orWhereIn('relate_id', Comment::visible()->select('id'));
            })
            ->whereFullText('text', $terms . '*', ['mode' => 'boolean'])
            ->with('relate')
            ->orderByRaw(...$order)
            ->paginate($perPage);

        $morphWith = array_filter(array_column(Registry::$search, 'with', 'class'));
        $posts->loadMorph('relate', [Comment::class => ['relate'], ...$morphWith]);

        return $posts;
    }
}
