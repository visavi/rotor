<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Автор записи — только то, чем его подписывают в списках
 *
 * @mixin User
 */
class AuthorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'login'  => $this->login,
            'name'   => $this->name,
            'level'  => $this->level,
            'color'  => $this->color,
            'avatar' => $this->avatar ? url($this->avatar) : null,
            'status' => $this->status ? $this->getStatus()->toHtml() : null,
        ];
    }
}
