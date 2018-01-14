@extends('layout')

@section('title')
    Панель управления
@stop

@section('content')

    <h1>Панель управления</h1>

    <i class="fa fa-key fa-lg"></i> <b><a href="/admin/upgrade">Версия {{ VERSION }}.{{ setting('buildversion') }}</a></b><br><br>

    <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Редактор</b></div>
    <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/chat">Админ-чат</a> ({{ statsChat() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/book">Гостевая книга</a> ({{ statsGuest() }})<br>
    <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/forum">Форум</a> ({{ statsForum() }})<br>
    <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/gallery">Галерея</a> ({{ statsGallery() }})<br>
    <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/blog">Блоги</a> ({{ statsBlog() }})<br>
    <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/newload">Новые публикации</a> ({{  statsNewLoad() }})<br>
    <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/reklama">Пользовательская реклама</a><br>

    @if (isAdmin('moder'))
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Модератор</b></div>
        <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/ban">Бан / Разбан</a><br>
        <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/banlist">Список забаненых</a> ({{ statsBanned() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/spam">Список жалоб</a> ({{ statsSpam() }})<br>
        <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/adminlist">Список старших</a> ({{ statsAdmins() }})<br>
        <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/reglist">Список ожидающих</a> ({{ statsRegList() }})<br>
        <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/votes">Голосования</a> ({{ statVotes() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/antimat">Управление антиматом</a> ({{ statsAntimat() }})<br>
        <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/banhist">История банов</a> ({{ statsBanHist() }})<br>
        <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/invitations">Приглашения</a> ({{ statsInvite() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/transfers">Денежные операции</a><br>
    @endif

    @if (isAdmin('admin'))
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Администратор</b></div>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/rules">Правила сайта</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/news">Новости</a> ({{ statsNews() }})<br>

        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/ipban">IP-бан панель</a> ({{ statsIpBanned() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/phpinfo">PHP-информация</a> ({{ parseVersion(PHP_VERSION) }})<br>
        <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/load">Загруз-центр</a> ({{ statsLoad() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/log">Ошибки / Автобаны</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/blacklist">Черный список</a> ({{ statsBlacklist() }})<br>
        <i class="fa fa-circle fa-lg text-muted"></i> <a href="/admin/offers">Предложения / Проблемы</a> ({{ statsOffers() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/smiles">Управление смайлами</a> ({{ statsSmiles() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/status">Статусы пользователей</a><br>
    @endif

    @if (isAdmin('boss'))
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Босс</b></div>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/setting">Настройки сайта</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/users">Пользователи</a> ({{ statsUsers() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/cache">Очистка кэша</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/backup">Backup-панель</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/checker">Сканирование сайта</a> ({{ statsChecker() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/delivery">Приват-рассылка</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/logadmin">Логи посещений</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/notice">Шаблоны писем</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/files">Редактирование файлов</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/delusers">Чистка пользователей</a><br>
    @endif

    @if (! $existBoss)
        <br><div class="b"><b><span style="color:#ff0000">Внимание!!! Отсутствует профиль суперадмина</span></b><br>
        В базе данных не найден пользователь с правами boss</div>
    @endif

    @if (file_exists(HOME.'/install'))
        <br><div class="b"><b><span style="color:#ff0000">Внимание!!! Необходимо удалить директорию install</span></b><br>
        Наличие этой директории может нарушить безопасность сайта. Удалите ее прямо сейчас!</div>
    @endif
@stop
