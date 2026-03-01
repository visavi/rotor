<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Forum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Forum */
class ForumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'parent_id'            => $this->parent_id,
            'sort'                 => $this->sort,
            'title'                => $this->title,
            'description'          => $this->description,
            'closed'               => $this->closed,
            'count_topics'         => $this->count_topics,
            'count_posts'          => $this->count_posts,
            'last_topic_id'        => $this->last_topic_id,
            'last_topic_title'     => $this->lastTopic->title,
            'last_post_user_login' => $this->lastTopic->lastPost->id ? $this->lastTopic->lastPost->user->login : null,
            'last_post_at'         => $this->lastTopic->lastPost->id ? dateFixed($this->lastTopic->lastPost->created_at, 'c', true) : null,
            'children'             => self::collection($this->whenLoaded('children')),
        ];
    }
}
