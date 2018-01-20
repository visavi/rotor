<?php

namespace App\Tasks;

use App\Models\Login;
use Crontask\Tasks\Task;

class DeleteLogin extends Task
{
    /**
     * Удаляет старые записи истории авторизаций
     */
    public function run()
    {
        Login::query()
            ->where('created_at', '<', SITETIME - 3600 * 24 * 30)
            ->delete();
    }
}
