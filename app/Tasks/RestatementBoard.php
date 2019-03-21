<?php

declare(strict_types=1);

namespace App\Tasks;

use Crontask\Tasks\Task;

class RestatementBoard extends Task
{
    /**
     * Пересчитывает количество объявлений
     */
    public function run()
    {
        restatement('boards');
    }
}

