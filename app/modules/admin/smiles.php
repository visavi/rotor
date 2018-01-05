<?php
view(setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$page = int(Request::input('page', 1));

if (! isAdmin([101, 102])) redirect('/admin/');

//show_title('Управление смайлами');

switch ($action):

/**
 * Удаление смайлов
 */
case 'del':


    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/smiles?page='.$page.'">Вернуться</a><br>';
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

view(setting('themes').'/foot');
