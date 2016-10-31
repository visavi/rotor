<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
include_once __DIR__.'/../app/start.php';

$params = App::router('params');
$target = App::router('target');

if ($target && is_callable($target)) {

    call_user_func_array($target, $params);

} elseif ($target) {

    $target = explode('@', $target);

    $act = isset($target[1]) ? $target[1] : 'index';

    if (isset($params['action'])) {
        $act = $params['action'];
    }

    include_once (APP.$target[0]);

} else {
    App::abort(404);
}
if (isset($_SESSION['input'])) unset($_SESSION['input']);


// Удалить карантин с сайта $config['karantin'], setting karantin, is_quarantine (сделано)
// Удалить дайджест (таблица visit)
// Удалить $config['cache']поле setting cache (сделано)
// Удалить выбор и загрузку аватара, сделать генерацию аватара из фото в профиле
// Удалить быстрый переход $config['navigation'] users_navigation (сделано)
// Удалить таблицу аватаров, настройки итд avatarupload avatarpoints avatarsize avatarweight avlist
// Удалить админску рекламу таблица advert (сделано)
// Удалить пирамиду таблица pyramid $config['showlink'] (сделано)

// ALTER TABLE `rotorcms`.`smiles` CHANGE COLUMN `smiles_name` `smiles_name` VARCHAR(100) NOT NULL ;
// UPDATE `smiles` SET `smiles_name`= CONCAT('/assets/img/smiles/', `smiles_name`);  и /upload/smiles системным
