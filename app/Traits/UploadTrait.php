<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;

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
            $imageManager = app(ImageManager::class);
            $image = $imageManager->read($file);

            if ($image->width() <= 100 && $image->height() <= 100) {
                $file->move(public_path($this->uploadPath), $filename);
            } else {
                $image->scaleDown(setting('screensize'), setting('screensize'));

                if (setting('copyfoto')) {
                    $image->place(
                        public_path('assets/img/images/watermark.png'),
                        'bottom-right',
                        10,
                        10
                    );
                }

                $image->save($fullPath);
            }
        } else {
            $file->move(public_path($this->uploadPath), $filename);
        }

        $filesize = filesize($fullPath);

        if ($record) {
            $upload = File::query()->create([
                'relate_id'   => $this->id ?? 0,
                'relate_type' => $this->getMorphClass(),
                'path'        => $path,
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
