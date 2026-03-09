<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Dialogue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Dialogue */
class NewMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'login'           => $this->author->exists ? $this->author->login : (string) $this->author_id,
            'name'            => $this->author_id ? $this->author->getName() : __('messages.system'),
            'count'           => (int) $this->getAttribute('cnt'),
            'last_message_at' => dateFixed($this->getAttribute('last_created_at'), 'c', true),
        ];
    }
}
