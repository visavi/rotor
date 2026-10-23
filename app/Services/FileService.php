<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use App\Models\File;
use App\Models\Message;
use App\Rules\ImageDimensionsRule;
use App\Support\Registry;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
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
     * Принимает ли тип в API файлы, загруженные до создания записи
     *
     * Личным сообщениям нельзя: ожидающий файл не знает получателя, и брошенный
     * в одном диалоге на сайте ушёл бы в другой. Их файлы — только в самом запросе
     */
    public static function acceptsPending(string $type): bool
    {
        return $type !== Message::$morphName;
    }

    /**
     * Строка для атрибута accept: расширения типа с точкой
     *
     * null при пустой настройке — пустой accept браузер понимает как «любые файлы»
     */
    public static function accept(string $type): ?string
    {
        $extensions = self::extensions($type);

        return $extensions ? implode(',', array_map(static fn ($ext) => '.' . $ext, $extensions)) : null;
    }

    /**
     * Загружает вложение к записи, id = 0 — запись еще не создана
     *
     * @return array{success: false, status: int, message: string}|array{success: true, file: File, data: array}
     */
    public function upload(?UploadedFile $file, string $type, int $id): array
    {
        $class = Relation::getMorphedModel($type);

        if (! $class || ! in_array($type, self::types(), true)) {
            return ['success' => false, 'status' => 422, 'message' => 'Type invalid'];
        }

        $isImageType = in_array($type, self::mediaTypes(), true);

        if ($id) {
            $model = $class::query()->find($id);

            if (! $model) {
                return ['success' => false, 'status' => 404, 'message' => 'Service not found'];
            }
        } else {
            $model = new $class();
        }

        if ($model->getKey() && $model->getAttribute('user_id') !== auth()->id() && ! isAdmin()) {
            return ['success' => false, 'status' => 403, 'message' => __('ajax.record_not_author')];
        }

        $uploadedFiles = File::query()
            ->where('relate_type', $type)
            ->where('relate_id', $id)
            ->where('user_id', auth()->id())
            ->get(['name']);

        $duplicate = $file && $uploadedFiles->contains(
            'name',
            Str::substr(getBodyName($file->getClientOriginalName()), 0, 50) . '.' . strtolower($file->getClientOriginalExtension())
        );

        // Сам файл проверяется последним: остальное дешевле и ответ на него понятнее
        /** @var string|null $error */
        $error = match (true) {
            $uploadedFiles->count() >= setting('maxfiles') => __('validator.files_max', ['max' => setting('maxfiles')]),
            $duplicate                                     => __('validator.file_duplicate'),
            default                                        => $this->fileError($file, $type),
        };

        // Без файла fileError() уже вернул ошибку
        if ($error || ! $file) {
            return ['success' => false, 'status' => 422, 'message' => $error ?? __('validator.file_upload_failed')];
        }

        $fileData = $this->store($model, $file, $isImageType);

        return [
            'success' => true,
            'file'    => File::query()->findOrFail((int) $fileData['id']),
            'data'    => $fileData,
        ];
    }

    /**
     * Проверяет загружаемый файл: размер, расширение, у картинки — не меньше 100 px
     *
     * @return string|null текст ошибки или null, если файл подходит
     */
    private function fileError(?UploadedFile $file, string $type): ?string
    {
        $validator = Validator::make(['file' => $file], [
            'file' => ['required', ...self::fileRules($type), new ImageDimensionsRule(100)],
        ], [
            'file.required'   => __('validator.file_upload_failed'),
            'file.file'       => __('validator.file_upload_failed'),
            'file.uploaded'   => __('validator.file_upload_failed'),
            'file.max'        => __('validator.size_max', ['size' => formatSize(setting('filesize'))]),
            'file.extensions' => __('validator.extension'),
            'file.mimes'      => __('validator.extension'),
        ]);

        return $validator->fails() ? $validator->errors()->first() : null;
    }

    /**
     * Правила валидации файлов, переданных прямо в запросе
     *
     * Набор расширений зависит от того, куда грузят: галерея принимает медиа,
     * файловые разделы — остальное. Лимит общий с файлами, загруженными
     * заранее, если тип их принимает: они тоже лягут в запись. Считаются
     * только когда файлы пришли — запрос без них не платит
     */
    public static function rules(string $type): array
    {
        $max = (int) setting('maxfiles');

        return [
            'files' => [
                'nullable',
                'array',
                static function (string $attribute, mixed $value, Closure $fail) use ($type, $max) {
                    if (! is_array($value)) {
                        return;
                    }

                    $taken = self::acceptsPending($type)
                        ? File::query()->pending($type, auth()->id())->count()
                        : 0;

                    if (count($value) + $taken > $max) {
                        $fail(__('validator.files_max', ['max' => $max]));
                    }
                },
            ],
            'files.*' => self::fileRules($type),
        ];
    }

    /**
     * Проверка одного файла — общая для формы, API и ajax-загрузки
     *
     * Файл сохраняется с расширением клиента, поэтому решает extensions: картинка
     * с именем x.html легла бы в uploads исполняемой страницей. Содержимое (mimes)
     * важно галерее, которая показывает файл картинкой или видео. Файловым
     * разделам оно не нужно: txt с json или csv внутри — обычный текстовый файл
     *
     * @return array<int, string>
     */
    private static function fileRules(string $type): array
    {
        $extensions = implode(',', self::extensions($type));

        return [
            'file',
            'extensions:' . $extensions,
            ...(in_array($type, self::mediaTypes(), true) ? ['mimes:' . $extensions] : []),
            'max:' . self::maxFileSize(),
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
     * Прикладывает к новой записи все её вложения: загруженные заранее и из запроса
     *
     * Загруженные заранее — только если тип их принимает (`acceptsPending`).
     * Сначала они: файлы из запроса считают sort от уже привязанных и встают
     * после них. В обратном порядке sort у групп совпадал, и вложения перемешивались
     *
     * @param array<int, UploadedFile> $files
     */
    public function attach(Model $model, array $files): void
    {
        if (self::acceptsPending($model->getMorphClass())) {
            $this->attachPending($model);
        }

        $this->attachUploaded($model, $files);
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
            ->pending($model->getMorphClass(), $userId ?? auth()->id())
            ->update(['relate_id' => $model->getKey()]);
    }

    /**
     * Удаляет вложение
     *
     * @return array{success: false, status: int, message: string}|array{success: true, path: string}
     */
    public function remove(int $fileId, string $type): array
    {
        if (! in_array($type, self::types(), true)) {
            return ['success' => false, 'status' => 422, 'message' => 'Type invalid'];
        }

        $file = File::query()
            ->where('relate_type', $type)
            ->find($fileId);

        if (! $file) {
            return ['success' => false, 'status' => 404, 'message' => 'File not found'];
        }

        if (Gate::denies('delete', $file)) {
            return ['success' => false, 'status' => 403, 'message' => __('ajax.record_not_author')];
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
     * @return array{success: false, status: int, message: string}|array{success: true}
     */
    public function sort(array $ids, string $type): array
    {
        if (! in_array($type, self::types(), true)) {
            return ['success' => false, 'status' => 422, 'message' => 'Type invalid'];
        }

        $ids = array_values(array_unique(array_filter($ids)));

        $files = File::query()
            ->where('relate_type', $type)
            ->whereKey($ids)
            ->get(['id', 'relate_id', 'user_id']);

        if (! $ids || $files->count() !== count($ids) || $files->pluck('relate_id')->unique()->count() !== 1) {
            return ['success' => false, 'status' => 404, 'message' => 'File not found'];
        }

        if ($files->contains(static fn (File $file) => Gate::denies('delete', $file))) {
            return ['success' => false, 'status' => 403, 'message' => __('ajax.record_not_author')];
        }

        // Id — целые, проверенные запросом выше, поэтому подставляются в SQL напрямую.
        // Не upsert: в строгом MySQL он требует все NOT NULL поля
        $cases = '';

        foreach ($ids as $index => $id) {
            $cases .= ' WHEN ' . $id . ' THEN ' . ($index + 1);
        }

        File::query()->whereKey($ids)->update(['sort' => DB::raw('CASE id' . $cases . ' END')]); // @phpstan-ignore argument.type (в SQL только целые, собранные выше)

        return ['success' => true];
    }

    /**
     * Сохраняет файл и запускает постобработку модели (видео, архивы)
     */
    private function store(object $model, UploadedFile $file, bool $isImageType): array
    {
        // Типы вложений — модели с UploadTrait
        $fileData = $model->uploadFile($file); // @phpstan-ignore method.notFound

        if (method_exists($model, 'convertVideo')) {
            $model->convertVideo($fileData);
        }

        if (! $isImageType && method_exists($model, 'addFileToArchive')) {
            $model->addFileToArchive($fileData);
        }

        return $fileData;
    }
}
