<?php

namespace App\Tasks;

use App\Models\Admlog;
use App\Models\Log;
use Crontask\Tasks\Task;

class DeleteLogs extends Task
{
    /**
     * Удаляет старые записи логов
     */
    public function run()
    {
        $time = strtotime('-1 month', SITETIME);

        Log::query()
            ->where('created_at', '<', $time)
            ->delete();

        Admlog::query()
            ->where('created_at', '<', $time)
            ->delete();
    }
}

