<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Message */
class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = $this->getAttribute('type');
        $sender = $type === Message::IN ? $this->author : $this->user;

        return [
            'id'             => $this->id,
            'login'          => $sender->exists ? $sender->login : (string) $this->author_id,
            'name'           => $this->author_id ? $sender->getName() : __('messages.system'),
            'text'           => $this->text,
            'type'           => $type,
            'recipient_read' => (bool) $this->getAttribute('recipient_read'),
            'created_at'     => dateFixed($this->created_at, 'c', true),
            'files'          => FileResource::collection($this->files),
        ];
    }
}
