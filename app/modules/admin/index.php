<?php
App::view(App::setting('themes').'/index');

if (! is_admin()) {
    redirect ('/');
}

//show_title('Панель управления');
?>
<i class="fa fa-key fa-lg"></i> <b><a href="/admin/upgrade">Версия <?= VERSION ?>.<?= App::setting('buildversion') ?></a></b><br /><br />

<div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Модератор</b></div>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/chat">Админ-чат</a> (<?=stats_chat()?>)<br />
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/book">Гостевая книга</a> (<?=stats_guest()?>)<br />
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/forum">Форум</a> (<?=stats_forum()?>)<br />
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/gallery">Галерея</a> (<?=stats_gallery()?>)<br />
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/blog">Блоги</a> (<?=stats_blog()?>)<br />
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/events">События</a> (<?=stats_events()?>)<br />
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/newload">Новые публикации</a> (<?=stats_newload()?>)<br />

    <?=show_admin_links(105);?>

    <?php if (is_admin([101, 102, 103])) {?>
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Старший модер</b></div>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/ban">Бан / Разбан</a><br />
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/banlist">Список забаненых</a> (<?=stats_banned()?>)<br />
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/spam">Список жалоб</a> (<?=stats_spam()?>)<br />
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/adminlist">Список старших</a> (<?=stats_admins()?>)<br />
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/reglist">Список ожидающих</a> (<?=stats_reglist()?>)<br />
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/votes">Голосования</a> (<?=stats_votes()?>)<br />
        <?=show_admin_links(103);?>
    <?php }?>

    <?php if (is_admin([101, 102])) {?>
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Администратор</b></div>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/rules">Правила сайта</a><br />
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/news">Новости</a> (<?=stats_allnews()?>)<br />
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/users">Пользователи</a> (<?=stats_users()?>)<br />
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/ipban">IP-бан панель</a> (<?=stats_ipbanned()?>)<br />
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/phpinfo">PHP-информация</a> (<?=phpversion()?>)<br />
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/load">Загруз-центр</a> (<?=stats_load()?>)<br />
        <?=show_admin_links(102);?>
    <?php }?>

    <?php if (is_admin([101])) {?>
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Суперадмин</b></div>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/setting">Настройки сайта</a><br />
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/cache">Очистка кэша</a><br />
        <?=show_admin_links(101);?>

        <?php if (App::getUsername() == App::setting('nickname')) {?>
            <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/files">Редактирование файлов</a><br />
            <?php show_admin_links();?>
        <?php }?>
    <?php }?>

    <?php if ($admin = user(App::setting('nickname'))) {?>
        <?php if ($admin['level'] != 101) {?>

            <br /><div class="b"><b><span style="color:#ff0000">Внимание!!! Cуперадминистратор не имеет достаточных прав!</span></b><br />
            Профилю назначен уровень доступа <b><?=$admin['level']?> - <?=user_status($admin['level'])?></b></div>

        <?php }?>

    <?php } else {?>

        <br /><div class="b"><b><span style="color:#ff0000">Внимание!!! Отсутствует профиль суперадмина</span></b><br />
        Профиль администратора <b><?=App::setting('nickname')?></b> не задействован на сайте</div>

    <?php }?>

    <?php if (file_exists(BASEDIR.'/install') && !empty(App::setting('nickname'))) {?>

        <br /><div class="b"><b><span style="color:#ff0000">Внимание!!! Необходимо удалить директорию install</span></b><br />
        Наличие этой директории может нарушить безопасность сайта. Удалите ее прямо сейчас!</div>

    <?php }?>

<?php App::view(App::setting('themes').'/foot'); ?>
