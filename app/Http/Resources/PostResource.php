<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'login'      => $this->user->login,
            'text'       => bbCode($this->text)->toHtml(),
            'rating'     => $this->rating,
            'files'      => FileResource::collection($this->files),
            'updated_at' => dateFixed($this->updated_at, 'c', true),
            'created_at' => dateFixed($this->created_at, 'c', true),
        ];
    }
}
