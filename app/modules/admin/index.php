<?php
view(setting('themes').'/index');

if (! isAdmin()) {
    redirect ('/');
}

//show_title('Панель управления');
?>
<i class="fa fa-key fa-lg"></i> <b><a href="/admin/upgrade">Версия <?= VERSION ?>.<?= setting('buildversion') ?></a></b><br><br>

<div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Модератор</b></div>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/chat">Админ-чат</a> (<?=statsChat()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/book">Гостевая книга</a> (<?=statsGuest()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/forum">Форум</a> (<?=statsForum()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/gallery">Галерея</a> (<?=statsGallery()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/blog">Блоги</a> (<?=statsBlog()?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/newload">Новые публикации</a> (<?=statsNewLoad()?>)<br>

    <?=showAdminLinks(105);?>

    <?php if (isAdmin([101, 102, 103])) {?>
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Старший модер</b></div>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/ban">Бан / Разбан</a><br>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/banlist">Список забаненых</a> (<?=statsBanned()?>)<br>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/spam">Список жалоб</a> (<?=statsSpam()?>)<br>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/adminlist">Список старших</a> (<?=statsAdmins()?>)<br>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/reglist">Список ожидающих</a> (<?=statsRegList()?>)<br>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/votes">Голосования</a> (<?=statVotes()?>)<br>
        <?=showAdminLinks(103);?>
    <?php }?>

    <?php if (isAdmin([101, 102])) {?>
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Администратор</b></div>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/rules">Правила сайта</a><br>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/news">Новости</a> (<?=statsNews()?>)<br>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/users">Пользователи</a> (<?=statsUsers()?>)<br>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/ipban">IP-бан панель</a> (<?=statsIpBanned()?>)<br>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/phpinfo">PHP-информация</a> (<?=phpversion()?>)<br>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/load">Загруз-центр</a> (<?=statsLoad()?>)<br>
        <?=showAdminLinks(102);?>
    <?php }?>

    <?php if (isAdmin([101])) {?>
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Суперадмин</b></div>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/setting">Настройки сайта</a><br>
        <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/cache">Очистка кэша</a><br>
        <?=showAdminLinks(101);?>

        <?php if (getUsername() == setting('nickname')) {?>
            <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/admin/files">Редактирование файлов</a><br>
            <?php showAdminLinks();?>
        <?php }?>
    <?php }?>

    <?php if ($admin = user(setting('nickname'))) {?>
        <?php if ($admin['level'] != 101) {?>

            <br><div class="b"><b><span style="color:#ff0000">Внимание!!! Cуперадминистратор не имеет достаточных прав!</span></b><br>
            Профилю назначен уровень доступа <b><?=$admin['level']?> - <?=userLevel($admin['level'])?></b></div>

        <?php }?>

    <?php } else {?>

        <br><div class="b"><b><span style="color:#ff0000">Внимание!!! Отсутствует профиль суперадмина</span></b><br>
        Профиль администратора <b><?=setting('nickname')?></b> не задействован на сайте</div>

    <?php }?>

    <?php if (file_exists(BASEDIR.'/install') && !empty(setting('nickname'))) {?>

        <br><div class="b"><b><span style="color:#ff0000">Внимание!!! Необходимо удалить директорию install</span></b><br>
        Наличие этой директории может нарушить безопасность сайта. Удалите ее прямо сейчас!</div>

    <?php }?>

<?php view(setting('themes').'/foot'); ?>
