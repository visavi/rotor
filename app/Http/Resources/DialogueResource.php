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
            'login'          => $this->author->exists ? $this->author->login : 0,
            'name'           => $this->author_id ? $this->author->getName() : __('messages.system'),
            'text'           => bbCode($this->text)->toHtml(),
            'type'           => $this->getAttribute('type'),
            'all_reading'    => (bool) $this->getAttribute('all_reading'),
            'recipient_read' => (bool) $this->getAttribute('recipient_read'),
            'created_at'     => dateFixed($this->created_at, 'c', true),
        ];
    }
}
