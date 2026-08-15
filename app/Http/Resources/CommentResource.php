<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesAttachments;
use App\Models\Comment;
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
}
