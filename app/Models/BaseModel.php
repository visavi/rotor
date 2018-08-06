<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManagerStatic as Image;

class BaseModel extends Model
{
    /**
     * Путь загрузки файлов
     *
     * @var string
     */
    public $uploadPath;

    /**
     * Записывать файлы в таблицу
     *
     * @var bool
     */
    public $dataRecord = true;

    /**
     * Возвращает связь пользователей
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault();
    }

    /**
     * Возвращает логин пользователя
     *
     * @param string $value
     * @return string
     */
    public function getLoginAttribute($value): string
    {
        return $value ?? setting('guestsuser');
    }

    /**
     * Загружает изображение
     *
     * @param  UploadedFile $file объект изображения
     * @return array              путь загруженного файла
     */
    public function uploadFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $basename  = basename($file->getClientOriginalName());
        $filename  = uniqueName($extension);
        $fullPath  = $this->uploadPath . '/' . $filename;
        $path      = str_replace(HOME, '', $fullPath);

        if (in_array($extension, ['jpg', 'jpeg', 'gif', 'png'], true)) {
            $img = Image::make($file);

            if ($img->getWidth() <= 100 && $img->getHeight() <= 100) {
                $file->move($this->uploadPath, $filename);
            } else {
                $img->resize(setting('screensize'), setting('screensize'), function ($constraint) {
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

        if ($this->dataRecord) {
            $upload = File::query()->create([
                'relate_id'   => (int) $this->id,
                'relate_type' => static::class,
                'hash'        => $path,
                'name'        => utfSubstr($basename, 0, 50) . '.' . $extension,
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
