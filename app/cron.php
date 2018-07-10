<?php

use Crontask\TaskList;

require __DIR__ . '/bootstrap.php';

$taskList = new TaskList();

$taskList->addTasks([
    (new App\Tasks\DeletePollings())->setExpression('@weekly'),
    (new App\Tasks\DeleteReaders())->setExpression('@weekly'),
    (new App\Tasks\DeleteLogin())->setExpression('@weekly'),
    (new App\Tasks\DeletePending())->setExpression('@daily'),
    (new App\Tasks\DeleteLogs())->setExpression('@daily'),
    (new App\Tasks\DeleteFiles())->setExpression('@daily'),
    (new App\Tasks\RestatementBoard())->setExpression('30 */3 * * *'),
    (new App\Tasks\AddSubscribers())->setExpression('@hourly'),
    (new App\Tasks\SendMessages())->setExpression('* * * * *'),
]);

$taskList->run();
