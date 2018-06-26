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
     * @param  UploadedFile $file       объект изображения
     * @param null          $uploadPath путь директории для загрузки
     * @return string                   путь загруженного файла
     */
    public function uploadFile(UploadedFile $file, $uploadPath = null): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename  = uniqueName($extension);
        $path      = $uploadPath ?? $this->uploadPath;

        if (in_array($extension, ['jpg', 'jpeg', 'gif', 'png'], true)) {
            $img = Image::make($file);

            if ($img->getWidth() <= 100 && $img->getHeight() <= 100) {
                $file->move($path, $filename);
            } else {
                $img->resize(setting('screensize'), setting('screensize'), function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                if (setting('copyfoto')) {
                    $img->insert(HOME . '/assets/img/images/watermark.png', 'bottom-right', 10, 10);
                }

                $img->save($path . '/' . $filename);
            }
        } else {
            $file->move($path, $filename);
        }

        if ($this->dataRecord) {
            File::query()->create([
                'relate_id'   => (int) $this->id,
                'relate_type' => static::class,
                'hash'        => $filename,
                'name'        => $file->getClientOriginalName(),
                'size'        => filesize($path . '/' . $filename),
                'user_id'     => getUser('id'),
                'created_at'  => SITETIME,
            ]);
        }

        return str_replace(HOME, '', $path) . '/' . $filename;
    }
}
