<?php

namespace App\Tasks;

use App\Models\Load;
use Crontask\Tasks\Task;

class DeleteLoads extends Task
{
    /**
     * Удаляет старые записи скачиваний
     */
    public function run()
    {
        Load::query()
            ->where('created_at', '>', SITETIME - 3600 * 24 * 90)
            ->delete();
    }
}

