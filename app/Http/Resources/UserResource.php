<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'login'       => $this->login,
            'name'        => $this->name,
            'level'       => $this->level,
            'country'     => $this->country,
            'city'        => $this->city,
            'info'        => $this->info,
            'site'        => $this->site,
            'gender'      => $this->gender,
            'birthday'    => $this->birthday,
            'visits'      => $this->visits,
            'allforum'    => $this->allforum,
            'allguest'    => $this->allguest,
            'allcomments' => $this->allcomments,
            'themes'      => $this->themes,
            'point'       => $this->point,
            'money'       => $this->money,
            'status'      => $this->status ? $this->getStatus()->toHtml() : null,
            'color'       => $this->color,
            'avatar'      => $this->avatar ? url($this->avatar) : null,
            'picture'     => $this->picture ? url($this->picture) : null,
            'rating'      => $this->rating,
            'language'    => $this->language,
            'timezone'    => $this->timezone,
            'lastlogin'   => dateFixed($this->updated_at, 'c', true),
        ];
    }
}
