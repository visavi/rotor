<?php

use Crontask\TaskList;

require __DIR__.'/bootstrap.php';

$taskList = new TaskList();

$taskList->addTasks([
    (new App\Tasks\DeletePollings())->setExpression('@weekly'),
    (new App\Tasks\DeleteReads())->setExpression('@weekly'),
    (new App\Tasks\DeleteLogs())->setExpression('@daily'),
    (new App\Tasks\AddSubscribers())->setExpression('@hourly'),
    (new App\Tasks\SendMessages())->setExpression('* * * * *'),
]);

$taskList->run();
