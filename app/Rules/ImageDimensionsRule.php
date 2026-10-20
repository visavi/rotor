<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Размеры загружаемой картинки в пикселях
 *
 * Не картинку пропускает: в медиа разрешено и видео, а dimensions у Laravel
 * его бы отклонил. Тип и расширение проверяют другие правила
 */
readonly class ImageDimensionsRule implements ValidationRule
{
    public function __construct(
        private ?int $min = null,
        private ?int $max = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! str_starts_with((string) $value->getMimeType(), 'image')) {
            return;
        }

        [$width, $height] = @getimagesize($value->getPathname()) ?: [0, 0];

        if ($this->max && ($width > $this->max || $height > $this->max)) {
            $fail(__('validator.weight_max', ['weight' => $this->max]));
        }

        if ($this->min) {
            if ($width < $this->min || $height < $this->min) {
                $fail(__('validator.weight_min', ['weight' => $this->min]));
            }
        } elseif (! $width || ! $height) {
            $fail(__('validator.weight_empty'));
        }
    }
}
