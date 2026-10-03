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
use Illuminate\Support\Facades\DB;
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
     * Расширения, которые принимает тип
     *
     * Один источник для проверки, правил api и форм: окно выбора файлов
     * не должно предлагать то, что сервер отклонит. Регистр приводится к
     * нижнему — расширение загруженного файла сравнивается так же
     *
     * @return array<int, string>
     */
    public static function extensions(string $type): array
    {
        return self::extensionList(in_array($type, self::mediaTypes(), true) ? 'media_extensions' : 'file_extensions');
    }

    /**
     * Список расширений из настройки: без пробелов, пустых и в нижнем регистре
     *
     * @return array<int, string>
     */
    public static function extensionList(string $setting): array
    {
        return array_values(array_filter(array_map(
            static fn (string $ext) => strtolower(trim($ext)),
            explode(',', (string) setting($setting)),
        )));
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
            $rules = [
                'minweight'  => 100,
                'maxsize'    => setting('filesize'),
                'extensions' => self::extensions($type),
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
        return [
            'files'   => ['nullable', 'array', 'max:' . setting('maxfiles')],
            'files.*' => ['file', 'max:' . self::maxFileSize(), 'mimes:' . implode(',', self::extensions($type))],
        ];
    }

    /**
     * Предельный размер файла в килобайтах
     *
     * Настройка хранится в байтах, а правило max у Laravel считает килобайты
     */
    public static function maxFileSize(): int
    {
        return (int) (setting('filesize') / 1024);
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
     * Сохраняет порядок вложений записи
     *
     * Файлы — одной записи (или одни ожидающие её, relate_id = 0), права — как на удаление
     *
     * @param array<int, int> $ids Id файлов в новом порядке
     *
     * @return array{success: bool, message?: string}
     */
    public function sort(array $ids, string $type, Validator $validator): array
    {
        if (! in_array($type, self::types(), true)) {
            return ['success' => false, 'message' => 'Type invalid'];
        }

        $ids = array_values(array_unique(array_filter($ids)));

        $files = File::query()
            ->where('relate_type', $type)
            ->whereKey($ids)
            ->get(['id', 'relate_id', 'user_id']);

        if (! $ids || $files->count() !== count($ids) || $files->pluck('relate_id')->unique()->count() !== 1) {
            return ['success' => false, 'message' => 'File not found'];
        }

        $validator->true(
            isAdmin() || $files->every(static fn (File $file) => $file->user_id === getUser('id')),
            __('ajax.record_not_author')
        );

        if (! $validator->isValid()) {
            return ['success' => false, 'message' => current($validator->getErrors())];
        }

        // Id — целые, проверенные запросом выше, поэтому подставляются в SQL напрямую.
        // Не upsert: в строгом MySQL он требует все NOT NULL поля
        $cases = '';

        foreach ($ids as $index => $id) {
            $cases .= ' WHEN ' . $id . ' THEN ' . ($index + 1);
        }

        File::query()->whereKey($ids)->update(['sort' => DB::raw('CASE id' . $cases . ' END')]);

        return ['success' => true];
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
