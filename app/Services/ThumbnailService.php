<?php

declare(strict_types=1);

namespace App\Services;

use FilesystemIterator;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\File as FileSystem;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

/**
 * Превью загруженных картинок.
 *
 * Создаётся при первом выводе картинки и ложится в public рядом с оригиналами,
 * дальше его отдаёт веб-сервер как обычную статику. Исходники при загрузке
 * уже ужаты до screensize, поэтому обработка на выводе страницы недорогая.
 * Превью сохраняется в webp: обычно он вдвое легче jpeg и в разы легче png.
 * Формат оригинала остаётся, если webp вышел тяжелее исходника (скриншот текста в png)
 * или GD собран без webp
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

        foreach (self::names($relative) as $name) {
            $thumbnail = self::DIRECTORY . '/' . $size . '/' . $name;

            if (is_file(public_path($thumbnail))) {
                return '/' . $thumbnail;
            }
        }

        $thumbnail = self::make($size, $relative);

        return $thumbnail === null ? $path : '/' . $thumbnail;
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
            foreach (self::names($relative) as $name) {
                FileSystem::delete(public_path(self::DIRECTORY . '/' . $size . '/' . $name));
            }
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
     * Создаёт превью и возвращает его путь внутри public, null — не вышло, страница покажет оригинал.
     * Любая ошибка здесь не должна ронять страницу
     */
    private static function make(int $size, string $relative): ?string
    {
        $source = public_path('uploads/' . $relative);

        if (! is_file($source)) {
            return null;
        }

        // Сразу после деплоя превью нет ни одного: без лимита первый показ
        // длинной ленты упрётся в max_execution_time. Остальные доделают следующие показы
        // Скрытый Context живёт один запрос или задачу очереди и не попадает в логи
        $made = Context::getHidden('thumbnails_made', 0);

        if ($made >= self::MAX_PER_REQUEST) {
            return null;
        }

        Context::addHidden('thumbnails_made', $made + 1);

        $temp = null;

        try {
            $info = getimagesize($source);

            if ($info === false) {
                return null;
            }

            $fits = $size === self::SQUARE
                ? $info[0] <= $size && $info[1] <= $size
                : $info[0] <= $size;

            $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
            $manager = app(ImageManager::class);
            $toWebp = $extension !== 'webp' && $manager->driver->supports(Format::WEBP);

            // Маленькую картинку, которую не во что переводить, копируем без decode
            $image = $fits && ! $toWebp ? null : $manager->decode($source);

            // Анимация не переживает пересохранение
            if ($image?->isAnimated()) {
                $image = null;
            }

            if ($image !== null && ! $fits) {
                $size === self::SQUARE
                    ? $image->coverDown($size, $size)
                    : $image->scaleDown(width: $size);
            }

            $name = $relative;
            $data = null;

            if ($image !== null && $toWebp) {
                $webp = (string) $image->encodeUsingFileExtension('webp', quality: self::QUALITY);

                if (strlen($webp) < filesize($source)) {
                    $name = self::webpName($relative);
                    $data = $webp;
                }
            }

            // Webp не вышел: уменьшенную картинку сохраняем в формате оригинала, маленькую копируем
            if ($data === null && $image !== null && ! $fits) {
                $data = (string) $image->encodeUsingFileExtension($extension, quality: self::QUALITY);
            }

            $thumbnail = self::DIRECTORY . '/' . $size . '/' . $name;
            $target = public_path($thumbnail);
            FileSystem::ensureDirectoryExists(dirname($target));

            // Пишем во временный файл и переименовываем: параллельный запрос
            // не отдаст недописанное превью
            $temp = dirname($target) . '/.' . uniqid('', true);

            $written = $data === null
                ? FileSystem::copy($source, $temp)
                : file_put_contents($temp, $data) !== false;

            return $written && rename($temp, $target) ? $thumbnail : null;
        } catch (Throwable) {
            return null;
        } finally {
            // После rename файла уже нет, остаётся только от неудачной попытки
            if ($temp !== null) {
                FileSystem::delete($temp);
            }
        }
    }

    /**
     * Имена, под которыми лежит превью: webp или формат оригинала
     *
     * @return list<string>
     */
    private static function names(string $relative): array
    {
        return array_values(array_unique([self::webpName($relative), $relative]));
    }

    /**
     * Имя превью в webp: имена загрузок уникальны, поэтому a.jpg и a.png не встречаются
     */
    private static function webpName(string $relative): string
    {
        return substr($relative, 0, -strlen(pathinfo($relative, PATHINFO_EXTENSION))) . 'webp';
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
