<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use App\Models\File;
use App\Models\Message;
use App\Support\Registry;
use App\Support\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Загрузка и удаление вложений — общее для сайта и API
 */
class FileService
{
    /**
     * Типы, куда грузят картинки и видео (галерея)
     */
    public static function mediaTypes(): array
    {
        return Registry::$mediaTypes;
    }

    /**
     * Типы, куда грузят обычные файлы
     */
    public static function fileTypes(): array
    {
        return array_merge([
            Comment::$morphName,
            Message::$morphName,
        ], Registry::$fileTypes);
    }

    /**
     * Все типы, принимающие вложения
     */
    public static function types(): array
    {
        return array_merge(self::mediaTypes(), self::fileTypes());
    }

    /**
     * Загружает вложение к записи, id = 0 — запись еще не создана
     *
     * @return array{success: bool, message?: string, file?: File, data?: array}
     */
    public function upload(?UploadedFile $file, string $type, int $id, Validator $validator): array
    {
        if (! in_array($type, self::types(), true)) {
            return ['success' => false, 'message' => 'Type invalid'];
        }

        $class = Relation::getMorphedModel($type);
        $isImageType = in_array($type, self::mediaTypes(), true);

        if ($id) {
            $model = $class::query()->find($id);

            if (! $model) {
                return ['success' => false, 'message' => 'Service not found'];
            }
        } else {
            $model = new $class();
        }

        $uploadedFiles = File::query()
            ->where('relate_type', $type)
            ->where('relate_id', $id)
            ->where('user_id', getUser('id'))
            ->get(['name']);

        $duplicate = $file && $uploadedFiles->contains(
            'name',
            Str::substr(getBodyName($file->getClientOriginalName()), 0, 50) . '.' . strtolower($file->getClientOriginalExtension())
        );

        $validator
            ->lt($uploadedFiles->count(), setting('maxfiles'), __('validator.files_max', ['max' => setting('maxfiles')]))
            ->false($duplicate, __('validator.file_duplicate'));

        if ($model->id) {
            $validator->true($model->user_id === getUser('id') || isAdmin(), __('ajax.record_not_author'));
        }

        if ($validator->isValid()) {
            $allowedExt = setting($isImageType ? 'media_extensions' : 'file_extensions');

            $rules = [
                'minweight'  => 100,
                'maxsize'    => setting('filesize'),
                'extensions' => explode(',', $allowedExt),
            ];

            $validator->file($file, $rules, __('validator.file_upload_failed'));
        }

        if (! $validator->isValid()) {
            return ['success' => false, 'message' => current($validator->getErrors())];
        }

        $fileData = $this->store($model, $file, $isImageType);

        return [
            'success' => true,
            'file'    => File::query()->find($fileData['id']),
            'data'    => $fileData,
        ];
    }

    /**
     * Правила валидации файлов, переданных прямо в запросе
     *
     * Набор расширений зависит от того, куда грузят: галерея принимает медиа,
     * файловые разделы — остальное
     */
    public static function rules(string $type): array
    {
        $extensions = in_array($type, self::mediaTypes(), true) ? 'media_extensions' : 'file_extensions';

        return [
            'files'   => ['nullable', 'array', 'max:' . setting('maxfiles')],
            'files.*' => ['file', 'max:' . setting('filesize'), 'mimes:' . setting($extensions)],
        ];
    }

    /**
     * Прикладывает к записи файлы, пришедшие в теле запроса
     *
     * @param array<int, UploadedFile> $files
     */
    public function attachUploaded(Model $model, array $files): void
    {
        $isImageType = in_array($model->getMorphClass(), self::mediaTypes(), true);

        foreach ($files as $file) {
            $this->store($model, $file, $isImageType);
        }
    }

    /**
     * Привязывает к записи вложения, загруженные до её создания
     *
     * Клиент грузит файлы с id = 0, они висят за пользователем и ждут записи —
     * так работает и форма на сайте, и POST /api/files
     *
     * @return int Количество привязанных файлов
     */
    public function attachPending(Model $model, ?int $userId = null): int
    {
        return File::query()
            ->where('relate_type', $model->getMorphClass())
            ->where('relate_id', 0)
            ->where('user_id', $userId ?? getUser('id'))
            ->update(['relate_id' => $model->getKey()]);
    }

    /**
     * Удаляет вложение
     *
     * @return array{success: bool, message?: string, path?: string}
     */
    public function remove(int $fileId, string $type, Validator $validator): array
    {
        if (! in_array($type, self::types(), true)) {
            return ['success' => false, 'message' => 'Type invalid'];
        }

        $file = File::query()
            ->where('relate_type', $type)
            ->find($fileId);

        if (! $file) {
            return ['success' => false, 'message' => 'File not found'];
        }

        $validator->true($file->user_id === getUser('id') || isAdmin(), __('ajax.record_not_author'));

        if (! $validator->isValid()) {
            return ['success' => false, 'message' => current($validator->getErrors())];
        }

        $file->delete();

        return ['success' => true, 'path' => $file->path];
    }

    /**
     * Сохраняет файл и запускает постобработку модели (видео, архивы)
     */
    private function store(object $model, UploadedFile $file, bool $isImageType): array
    {
        $fileData = $model->uploadFile($file);

        if (method_exists($model, 'convertVideo')) {
            $model->convertVideo($fileData);
        }

        if (! $isImageType && method_exists($model, 'addFileToArchive')) {
            $model->addFileToArchive($fileData);
        }

        return $fileData;
    }
}
