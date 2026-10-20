<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Rules\ImageDimensionsRule;
use App\Services\FileService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Фото профиля: одни правила на сайт и API
 *
 * Только картинка: из неё режется аватар, видео из медиа-расширений не подойдёт
 */
class PhotoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'photo' => [
                'required',
                'image',
                'max:' . FileService::maxFileSize(),
                'mimes:' . implode(',', FileService::extensionList('media_extensions')),
                new ImageDimensionsRule(100),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => __('validator.image_upload_failed'),
            'photo.uploaded' => __('validator.image_upload_failed'),
            'photo.image'    => __('validator.extension'),
            'photo.mimes'    => __('validator.extension'),
            'photo.max'      => __('validator.size_max', ['size' => formatSize(setting('filesize'))]),
        ];
    }
}
