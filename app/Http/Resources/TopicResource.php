<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Topic */
class TopicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'title'                => $this->title,
            'login'                => $this->user->login,
            'closed'               => $this->closed,
            'locked'               => $this->locked,
            'count_posts'          => $this->count_posts,
            'visits'               => $this->visits,
            'moderators'           => $this->moderators,
            'note'                 => $this->note,
            'last_post_id'         => $this->last_post_id,
            'last_post_user_login' => $this->lastPost->user->login,
            'close_user_id'        => $this->close_user_id,
            'updated_at'           => $this->updated_at,
            'created_at'           => $this->created_at,
        ];
    }
}
