<?php

declare(strict_types=1);

namespace App\Services;

use FilesystemIterator;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\File as FileSystem;
use Intervention\Image\ImageManager;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

/**
 * Превью загруженных картинок.
 *
 * Создаётся при первом выводе картинки и ложится в public рядом с оригиналами,
 * дальше его отдаёт веб-сервер как обычную статику. Исходники при загрузке
 * уже ужаты до screensize, поэтому обработка на выводе страницы недорогая
 */
final class ThumbnailService
{
    /** Уменьшить до ширины блока медиа (--media-max-width в main.css) */
    public const int MEDIA = 700;

    /** Квадратная миниатюра: на экране 50px, запас под плотные экраны */
    public const int SQUARE = 150;

    public const array SIZES = [self::MEDIA, self::SQUARE];

    /** Каталог превью внутри public */
    public const string DIRECTORY = 'uploads/thumbnails';

    /** Gif не уменьшаем: анимация не переживает пересохранение. Svg и так лёгкий */
    private const array EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private const int QUALITY = 80;

    /** Сколько превью создаёт один запрос */
    private const int MAX_PER_REQUEST = 10;

    /**
     * Адрес превью, при необходимости создаёт его.
     * Исходный адрес, если превью для файла не делается или не получилось
     */
    public static function url(string $path, int $size): string
    {
        $relative = self::relativePath($path);

        if ($relative === null || ! in_array($size, self::SIZES, true)) {
            return $path;
        }

        $thumbnail = self::DIRECTORY . '/' . $size . '/' . $relative;

        if (is_file(public_path($thumbnail)) || self::make($size, $relative, public_path($thumbnail))) {
            return '/' . $thumbnail;
        }

        return $path;
    }

    /**
     * Удаляет все превью файла
     */
    public static function delete(string $path): void
    {
        $relative = self::relativePath($path);

        if ($relative === null) {
            return;
        }

        foreach (self::SIZES as $size) {
            FileSystem::delete(public_path(self::DIRECTORY . '/' . $size . '/' . $relative));
        }
    }

    /**
     * Удаляет все превью сайта: они создадутся заново при показе картинок.
     * false — часть файлов удалить не удалось, например нет прав
     */
    public static function clear(): bool
    {
        $directory = public_path(self::DIRECTORY);

        return ! is_dir($directory) || FileSystem::deleteDirectory($directory);
    }

    /**
     * Сколько файлов и байт в каждом каталоге превью.
     * Считается всё, что удалит clear(): и каталоги размеров, которых больше нет, и брошенные временные файлы
     *
     * @return array<int|string, array{count: int, size: int}> Ключ — имя каталога, обычно размер
     */
    public static function stats(): array
    {
        $stats = [];

        foreach (glob(public_path(self::DIRECTORY) . '/*', GLOB_ONLYDIR) ?: [] as $directory) {
            $count = $size = 0;
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $count++;
                    $size += $file->getSize();
                }
            }

            $stats[basename($directory)] = ['count' => $count, 'size' => $size];
        }

        ksort($stats, SORT_NATURAL);

        return $stats;
    }

    /**
     * Создаёт превью, false — не вышло, страница покажет оригинал.
     * Любая ошибка здесь не должна ронять страницу
     */
    private static function make(int $size, string $relative, string $target): bool
    {
        $source = public_path('uploads/' . $relative);

        if (! is_file($source)) {
            return false;
        }

        // Сразу после деплоя превью нет ни одного: без лимита первый показ
        // длинной ленты упрётся в max_execution_time. Остальные доделают следующие показы
        // Скрытый Context живёт один запрос или задачу очереди и не попадает в логи
        $made = Context::getHidden('thumbnails_made', 0);

        if ($made >= self::MAX_PER_REQUEST) {
            return false;
        }

        Context::addHidden('thumbnails_made', $made + 1);

        $temp = null;

        try {
            $info = getimagesize($source);

            if ($info === false) {
                return false;
            }

            FileSystem::ensureDirectoryExists(dirname($target));

            // Пишем во временный файл и переименовываем: параллельный запрос
            // не отдаст недописанное превью
            $temp = dirname($target) . '/.' . uniqid('', true) . '.' . pathinfo($target, PATHINFO_EXTENSION);

            $fits = $size === self::SQUARE
                ? $info[0] <= $size && $info[1] <= $size
                : $info[0] <= $size;

            $image = $fits ? null : app(ImageManager::class)->decode($source);

            // Маленькую картинку не пересохраняем: копия не хуже, а jpeg не теряет качество
            if ($image === null || $image->isAnimated()) {
                FileSystem::copy($source, $temp);
            } else {
                $size === self::SQUARE
                    ? $image->coverDown($size, $size)
                    : $image->scaleDown(width: $size);

                $image->save($temp, quality: self::QUALITY);
            }

            return rename($temp, $target);
        } catch (Throwable) {
            return false;
        } finally {
            // После rename файла уже нет, остаётся только от неудачной попытки
            if ($temp !== null) {
                FileSystem::delete($temp);
            }
        }
    }

    /**
     * Путь внутри uploads для файла, у которого бывает превью
     */
    private static function relativePath(string $path): ?string
    {
        if (! str_starts_with($path, '/uploads/')) {
            return null;
        }

        $relative = substr($path, strlen('/uploads/'));

        if (
            str_contains($relative, '..')
            || str_starts_with($relative, 'thumbnails/')
            || ! in_array(strtolower(pathinfo($relative, PATHINFO_EXTENSION)), self::EXTENSIONS, true)
        ) {
            return null;
        }

        return $relative;
    }
}
