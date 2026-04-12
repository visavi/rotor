<?php

namespace App\Casts;

use App\Classes\HtmlSanitizer;
use App\Classes\StickerResolver;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class HtmlCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return StickerResolver::resolve($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value !== null ? HtmlSanitizer::sanitize($value) : null;
    }
}
