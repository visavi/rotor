<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesAttachments;
use App\Services\FeedService;
use App\Services\RatingService;
use App\Support\Registry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Model */
class FeedResource extends JsonResource
{
    use ResolvesAttachments;

    public function toArray(Request $request): array
    {
        $post = $this->resource;
        $type = $post->getMorphClass();
        $config = FeedService::typeConfig($type);

        // Носитель контента может отличаться от записи ленты (тема форума -> последний пост)
        $source = isset($config['source']) ? ($config['source'])($post) : $post;

        // Голосуют не всегда за саму запись ленты (в теме форума — за последнее сообщение)
        [$voteType, $voteId] = FeedService::pollTarget($post);
        $canVote = in_array($voteType, RatingService::types(), true) && $voteId;

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
}
