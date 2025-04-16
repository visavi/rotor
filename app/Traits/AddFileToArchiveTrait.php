<?php

declare(strict_types=1);

namespace App\Traits;

use ZipArchive;

trait AddFileToArchiveTrait
{
    /**
     * Add file to archive
     */
    public function addFileToArchive(array $file): void
    {
        if (
            $file['extension'] === 'zip'
            && setting('archive_file_path')
            && ! str_contains(setting('archive_file_path'), '..')
            && file_exists(public_path(setting('archive_file_path')))
        ) {
            $archive = new ZipArchive();
            $opened = $archive->open(public_path($file['path']));

            if ($opened === true) {
                $archive->addFile(public_path(setting('archive_file_path')), basename(setting('archive_file_path')));
                $archive->close();
            }
        }
    }
}
