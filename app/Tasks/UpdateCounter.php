<?php

namespace App\Tasks;

use App\Classes\Metrika;
use Crontask\Tasks\Task;

class UpdateCounter extends Task
{
    /**
     * Формирует счетчик
     */
    public function run()
    {
        $metrika = new Metrika();
        $metrika->getCounter();
    }
}
