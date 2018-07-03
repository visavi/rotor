<?php

namespace App\Tasks;

use App\Models\Reader;
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

