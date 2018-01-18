<?php

namespace App\Tasks;

use App\Models\User;
use Crontask\Tasks\Task;

class DeletePending extends Task
{
    /**
     * Удаляет не активированные аккаунты
     */
    public function run()
    {
        if (setting('regkeys')) {

            $users = User::query()
                ->where('level', User::PENDED)
                ->where('joined', '<', date('Y-m-d', strtotime('-1 day', SITETIME)))
                ->get();

            foreach($users as $user) {
                deleteUser($user);
            }
        }
    }
}
