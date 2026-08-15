<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Classes\Feed;
use App\Classes\Rating;
use App\Classes\Registry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin Model */
class FeedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $post = $this->resource;
        $type = $post->getMorphClass();
        $config = Feed::typeConfig($type);

        // Носитель контента может отличаться от записи ленты (тема форума -> последний пост)
        $source = isset($config['source']) ? ($config['source'])($post) : $post;

        // Голосуют не всегда за саму запись ленты (в теме форума — за последнее сообщение)
        [$voteType, $voteId] = Feed::pollTarget($post);
        $canVote = in_array($voteType, Rating::types(), true) && $voteId;

        return array_merge([
            'type'    => $type,
            'id'      => $post->getKey(),
            'section' => Registry::$labelTypes[$type] ?? null,
            'title'   => $this->resolveTitle($post, $config),
            // Ссылку и путь до раздела знает сама запись, лента их не конфигурирует
            'url'         => method_exists($post, 'getViewUrl') ? $post->getViewUrl() : null,
            'breadcrumbs' => method_exists($post, 'getBreadcrumbs') ? $post->getBreadcrumbs() : [],
            'text'        => $source?->getAttribute('text') ? absolutizeUrls($source->text) : null,
            'rating'      => $source?->getAttribute('rating'),
            // Цель голосования и голос текущего пользователя ('+', '-' или null)
            'vote' => $canVote ? [
                'type'  => $voteType,
                'id'    => $voteId,
                'value' => $post->getAttribute('user_vote'),
                'own'   => $source?->getAttribute('user_id') === getUser('id'),
            ] : null,
            'comments_count' => $post->getAttribute('count_comments') ?? $post->getAttribute('count_posts'),
            'user'           => $source?->user ? AuthorResource::make($source->user) : null,
            // Медиа идут в галерею, остальные вложения — списком, как в вёрстке сайта
            'media'      => FileResource::collection($this->resolveMedia($source)),
            'files'      => FileResource::collection($this->resolveFiles($source)),
            'created_at' => dateFixed($post->created_at, 'c', true),
        ], isset($config['api']) ? ($config['api'])($post) : []);
    }

    /**
     * Заголовок записи, для комментариев берётся из связанной записи
     */
    private function resolveTitle(Model $post, array $config): ?string
    {
        if (isset($config['title'])) {
            return ($config['title'])($post);
        }

        return $post->getAttribute('title');
    }

    /**
     * Вложения записи без картинок и видео, только если связь загружена
     */
    private function resolveFiles(?Model $source): Collection
    {
        if ($source && $source->relationLoaded('files') && method_exists($source, 'getFiles')) {
            return $source->getFiles();
        }

        return collect();
    }

    /**
     * Картинки и видео записи, кроме вставленных в текст — их клиент и так покажет в тексте
     */
    private function resolveMedia(?Model $source): Collection
    {
        if ($source && $source->relationLoaded('files') && method_exists($source, 'getDetachedMedia')) {
            return $source->getDetachedMedia();
        }

        return collect();
    }
}
