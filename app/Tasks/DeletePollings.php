<?php

declare(strict_types=1);

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
            ->where('created_at', '<', strtotime('-1 year', SITETIME))
            ->delete();
    }
}
