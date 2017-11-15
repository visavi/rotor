<?php

namespace App\Tasks;

use App\Models\Read;
use Crontask\Tasks\Task;

class DeleteReads extends Task
{
    /**
     * Удаляет старые записи статистики просмотров и скачиваний
     */
    public function run()
    {
        Read::query()
            ->where('created_at', '>', SITETIME - 3600 * 24 * 90)
            ->delete();
    }
}

