<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesAttachments;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Comment */
class CommentResource extends JsonResource
{
    use ResolvesAttachments;

    public function toArray(Request $request): array
    {
        $deleted = $this->deleted_at !== null;

        return [
            'id'        => $this->id,
            'parent_id' => $this->parent_id,
            'depth'     => $this->depth,
            // Комментарии приходят лентой по дате, родитель может остаться на другой
            // странице — поэтому ответ несёт краткий контекст с собой
            'parent' => $this->resolveParent(),
            // Запись, к которой оставлен комментарий, — в общих лентах
            'relate' => $this->whenLoaded('relate', fn () => $this->resolveRelate()),
            // Удалённые остаются в выдаче заглушкой, иначе ветка ответов рвётся
            'deleted' => $deleted,
            'text'    => $deleted ? null : absolutizeUrls($this->text),
            'rating'  => $this->rating,
            'vote'    => [
                'type'  => Comment::$morphName,
                'id'    => $this->id,
                'value' => $this->getAttribute('vote'),
                'own'   => $this->user_id === getUser('id'),
            ],
            'user'       => $deleted ? null : AuthorResource::make($this->user),
            'media'      => FileResource::collection($this->resolveMedia($this->resource)),
            'files'      => FileResource::collection($this->resolveFiles($this->resource)),
            'created_at' => dateFixed($this->created_at, 'c', true),
        ];
    }

    /**
     * Запись комментария: тип и id для перехода, заголовок для ленты
     */
    private function resolveRelate(): ?array
    {
        /** @var ?Model $relate */
        $relate = $this->relate;

        // Скрытая с сайта запись (неопубликованная статья, непроверенный файл) — без заголовка
        if (! $relate || (array_key_exists('active', $relate->getAttributes()) && ! $relate->getAttribute('active'))) {
            return null;
        }

        return [
            'type'  => $this->relate_type,
            'id'    => $relate->getKey(),
            'title' => e((string) $relate->getAttribute('title')),
        ];
    }

    /**
     * Комментарий, на который отвечают: автор и начало текста
     */
    private function resolveParent(): ?array
    {
        if (! $this->parent_id || ! $this->relationLoaded('parent')) {
            return null;
        }

        /** @var ?Comment $parent */
        $parent = $this->parent;

        if (! $parent) {
            return null;
        }

        return [
            'id'      => $parent->id,
            'login'   => $parent->deleted_at ? null : $parent->user->login,
            'excerpt' => $parent->deleted_at ? null : mb_substr(strip_tags($parent->text), 0, 100),
        ];
    }
}
