<?php

namespace App\Tasks;

use App\Models\Log;
use App\Models\Error;
use Crontask\Tasks\Task;

class DeleteLogs extends Task
{
    /**
     * Удаляет старые записи логов
     */
    public function run()
    {
        $time = strtotime('-1 month', SITETIME);

        Error::query()
            ->where('created_at', '<', $time)
            ->delete();

        Log::query()
            ->where('created_at', '<', $time)
            ->delete();
    }
}

