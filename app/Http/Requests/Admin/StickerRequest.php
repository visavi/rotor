<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Sticker;
use App\Models\StickersCategory;
use App\Rules\ImageDimensionsRule;
use App\Services\FileService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StickerRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'cid'  => ['required', 'integer', Rule::exists(StickersCategory::class, 'id')],
            'code' => [
                'required',
                'string',
                'between:2,20',
                'regex:/^[\p{L}\p{N}_\-]+$/iu',
                Rule::unique(Sticker::class, 'code')->ignore($this->route('id')),
            ],
        ];

        // Картинка загружается только при создании
        if (! $this->route('id')) {
            $rules['sticker'] = [
                'required',
                'file',
                'max:' . (int) (setting('stickermaxsize') / 1024),
                'extensions:' . implode(',', FileService::extensionList('media_extensions')),
                new ImageDimensionsRule((int) setting('stickerminweight'), (int) setting('stickermaxweight')),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'cid'                => __('stickers.category_not_exist'),
            'code.regex'         => __('stickers.valid_sticker_code'),
            'code.unique'        => __('stickers.sticker_exists'),
            'code'               => __('stickers.sticker_length'),
            'sticker.required'   => __('validator.image_upload_failed'),
            'sticker.file'       => __('validator.image_upload_failed'),
            'sticker.uploaded'   => __('validator.image_upload_failed'),
            'sticker.max'        => __('validator.size_max', ['size' => formatSize(setting('stickermaxsize'))]),
            'sticker.extensions' => __('validator.extension'),
        ];
    }

    /**
     * Код стикера хранится в нижнем регистре
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['code' => $this->string('code')->lower()->value()]);
    }
}
