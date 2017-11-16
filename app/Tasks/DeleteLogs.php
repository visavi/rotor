<?php

namespace App\Tasks;

use App\Models\Log;
use Crontask\Tasks\Task;

class DeleteLogs extends Task
{
    /**
     * Удаляет старые записи логов ошибок
     */
    public function run()
    {
        Log::query()
            ->where('created_at', '<', SITETIME - 3600 * 24 * 30)
            ->delete();
    }
}

