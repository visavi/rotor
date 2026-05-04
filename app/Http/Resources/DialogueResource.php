<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Message */
class DialogueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'login'          => $this->author->exists ? $this->author->login : (string) $this->author_id,
            'name'           => $this->author_id ? $this->author->getName() : __('messages.system'),
            'text'           => absolutizeUrls($this->text),
            'type'           => $this->getAttribute('type'),
            'all_reading'    => (bool) $this->getAttribute('all_reading'),
            'recipient_read' => (bool) $this->getAttribute('recipient_read'),
            'can_reply'      => (bool) $this->author_id,
            'created_at'     => dateFixed($this->created_at, 'c', true),
        ];
    }
}
