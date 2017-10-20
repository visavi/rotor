<?php

use Crontask\TaskList;

require __DIR__.'/bootstrap.php';

$taskList = new TaskList();

$taskList->addTask((new App\Tasks\AddSubscribers())->setExpression('@hourly'));
$taskList->addTask((new App\Tasks\SendMessages())->setExpression('* * * * *'));

$taskList->run();
