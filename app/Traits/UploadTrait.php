<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManagerStatic as Image;

trait UploadTrait
{
    /**
     * Загружает изображение
     *
     * @param UploadedFile $file Объект изображения
     */
    public function uploadFile(UploadedFile $file, bool $record = true): array
    {
        $mime = $file->getClientMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        $basename = getBodyName($file->getClientOriginalName());
        $basename = utfSubstr($basename, 0, 50) . '.' . $extension;
        $filename = uniqueName($extension);
        $path = $this->uploadPath . '/' . $filename;
        $fullPath = public_path($path);
        $isImage = str_starts_with($file->getMimeType(), 'image');

        if ($isImage) {
            $img = Image::make($file);

            if ($img->getWidth() <= 100 && $img->getHeight() <= 100) {
                $file->move(public_path($this->uploadPath), $filename);
            } else {
                $img->resize(setting('screensize'), setting('screensize'), static function (Constraint $constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                if (setting('copyfoto')) {
                    $img->insert(public_path('assets/img/images/watermark.png'), 'bottom-right', 10, 10);
                }

                $img->save($fullPath);
            }
        } else {
            $file->move(public_path($this->uploadPath), $filename);
        }

        $filesize = filesize($fullPath);

        if ($record) {
            $upload = File::query()->create([
                'relate_id'   => $this->id ?? 0,
                'relate_type' => $this->getMorphClass(),
                'hash'        => $path,
                'name'        => $basename,
                'size'        => $filesize,
                'user_id'     => getUser('id'),
                'created_at'  => SITETIME,
            ]);
        }

        return [
            'id'        => $upload->id ?? 0,
            'path'      => $path,
            'name'      => $basename,
            'extension' => $extension,
            'mime'      => $mime,
            'size'      => formatSize($filesize),
            'type'      => $isImage ? 'image' : 'file',
        ];
    }
}
