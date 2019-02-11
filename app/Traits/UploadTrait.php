<?php

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
     * @param  UploadedFile $file объект изображения
     * @param  bool         $record
     * @return array              путь загруженного файла
     */
    public function uploadFile(UploadedFile $file, $record = true): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $basename  = getBodyName($file->getClientOriginalName());
        $basename  = utfSubstr($basename, 0, 50) . '.' . $extension;
        $filename  = uniqueName($extension);
        $fullPath  = $this->uploadPath . '/' . $filename;
        $path      = str_replace(HOME, '', $fullPath);

        if (\in_array($extension, ['jpg', 'jpeg', 'gif', 'png'], true)) {
            $img = Image::make($file);

            if ($img->getWidth() <= 100 && $img->getHeight() <= 100) {
                $file->move($this->uploadPath, $filename);
            } else {
                $img->resize(setting('screensize'), setting('screensize'), function (Constraint $constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                if (setting('copyfoto')) {
                    $img->insert(HOME . '/assets/img/images/watermark.png', 'bottom-right', 10, 10);
                }

                $img->save($fullPath);
            }
        } else {
            $file->move($this->uploadPath, $filename);
        }

        if ($record) {
            $upload = File::query()->create([
                'relate_id'   => (int) $this->id,
                'relate_type' => static::class,
                'hash'        => $path,
                'name'        => $basename,
                'size'        => filesize($fullPath),
                'user_id'     => getUser('id'),
                'created_at'  => SITETIME,
            ]);
        }

        return [
            'id'        => $upload->id ?? 0,
            'path'      => $path,
            'extension' => $extension,
        ];
    }
}
