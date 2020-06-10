<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Models\File;
use Crontask\Tasks\Task;

class DeleteFiles extends Task
{
    /**
     * Удаляет не прикрепленные файлы
     */
    public function run()
    {
        $files = File::query()
            ->where('relate_id', 0)
            ->where('created_at', '<', strtotime('-1 day', SITETIME))
            ->get();

        foreach ($files as $file) {
            $file->delete();
        }
    }
}
