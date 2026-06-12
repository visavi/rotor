<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait FilesTrait
{
    /**
     * Возвращает загруженные файлы
     *
     * @return MorphMany<File, $this>
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate')
            ->orderBy('created_at');
    }

    /**
     * Возвращает файлы
     *
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files->filter(static fn (File $f) => ! $f->isImage() && ! $f->isVideo());
    }

    /**
     * Возвращает медиафайлы (картинки и видео)
     *
     * @return Collection<int, File>
     */
    public function getMedia(): Collection
    {
        return $this->files->filter(static fn (File $f) => $f->isImage() || $f->isVideo());
    }

    /**
     * Возвращает медиафайлы, не вставленные в текст
     *
     * @return Collection<int, File>
     */
    public function getDetachedMedia(): Collection
    {
        return $this->getMedia()->reject(fn (File $f) => str_contains($this->text ?? '', $f->path));
    }
}
