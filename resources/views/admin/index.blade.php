@extends('layout')

@section('title')
    Панель управления
@stop

@section('content')

    <h1>Панель управления</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Панель</li>
        </ol>
    </nav>

    <i class="fa fa-key fa-lg"></i> <b><a href="/admin/upgrade">Версия {{ VERSION }}.{{ setting('buildversion') }}</a></b><br><br>

    <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Редактор</b></div>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/chats">Админ-чат</a> ({{ statsChat() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/guestbooks">Гостевая книга</a> ({{ statsGuestbook() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/forums">Форум</a> ({{ statsForum() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/photos">Галерея</a> ({{ statsPhotos() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/blogs">Блоги</a> ({{ statsBlog() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/boards">Объявления</a> ({{ statsBoard() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/reklama">Пользовательская реклама</a><br>

    @if (isAdmin('moder'))
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Модератор</b></div>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/bans">Бан / Разбан</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/banlists">Забаненные</a> ({{ statsBanned() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/spam">Жалобы</a> ({{ statsSpam() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/administrators">Администрация</a> ({{ statsAdmins() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/reglists">Ожидающие</a> ({{ statsRegList() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/votes">Голосования</a> ({{ statVotes() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/antimat">Антимат</a> ({{ statsAntimat() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/banhists">История банов</a> ({{ statsBanHist() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/invitations">Приглашения</a> ({{ statsInvite() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/transfers">Денежные операции</a><br>
    @endif

    @if (isAdmin('admin'))
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Администратор</b></div>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/rules">Правила сайта</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/news">Новости</a> ({{ statsNews() }})<br>

        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/ipbans">IP-бан панель</a> ({{ statsIpBanned() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/phpinfo">PHP-информация</a> ({{ parseVersion(PHP_VERSION) }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/loads">Загруз-центр</a> ({{ statsLoad() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/downs/new">Новые публикации</a> ({{ statsNewLoad() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/errors">Ошибки / Автобаны</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/blacklists">Черный список</a> ({{ statsBlacklist() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/offers">Предложения / Проблемы</a> ({{ statsOffers() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/stickers">Стикеры</a> ({{ statsStickers() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/status">Статусы пользователей</a><br>
    @endif

    @if (isAdmin('boss'))
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>Босс</b></div>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/settings">Настройки сайта</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/users">Пользователи</a> ({{ statsUsers() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/caches">Очистка кэша</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/backups">Backup-панель</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/checkers">Сканирование сайта</a> ({{ statsChecker() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/delivery">Приват-рассылка</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/logs">Логи посещений</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/notices">Шаблоны писем</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/files">Редактирование страниц</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/delusers">Чистка пользователей</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/modules">Модули</a><br>
    @endif

    @if (! $existBoss)
        <div class="p-1 bg-danger text-white">
            <i class="fas fa-exclamation-triangle"></i>
            Внимание! Отсутствует профиль суперадмина<br>
            В базе данных не найден пользователь с правами boss
        </div>
    @endif

    @if (file_exists(HOME.'/install'))
        <div class="p-1 bg-danger text-white">
            <i class="fas fa-exclamation-triangle"></i>
            Внимание! Необходимо удалить директорию install<br>
            Наличие этой директории может нарушить безопасность сайта. Удалите ее прямо сейчас!
        </div>
    @endif
@stop
