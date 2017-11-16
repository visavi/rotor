<?php

namespace App\Tasks;

use App\Models\Polling;
use Crontask\Tasks\Task;

class DeletePollings extends Task
{
    /**
     * Удаляет старые записи голосов
     */
    public function run()
    {
        Polling::query()
            ->where('created_at', '<', SITETIME - 3600 * 24 * 365)
            ->delete();
    }
}
