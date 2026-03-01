<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;

/** @mixin User */
class UserProfileResource extends UserResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'email'     => $this->email,
            'phone'     => $this->phone,
            'allprivat' => $this->getCountMessages(),
            'newprivat' => $this->newprivat,
            'newwall'   => $this->newwall,
        ]);
    }
}
