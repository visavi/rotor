@extends('layout')

@section('title')
    {{ trans('index.panel') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('index.panel') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-key fa-lg"></i> <b><a href="/admin/upgrade">{{ trans('main.version') }} {{ VERSION }}.{{ setting('buildversion') }}</a></b><br><br>

    <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>{{ trans('main.editor') }}</b></div>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/chats">{{ trans('index.admin_chat') }}</a> ({{ statsChat() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/guestbooks">{{ trans('index.guestbooks') }}</a> ({{ statsGuestbook() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/forums">{{ trans('index.forums') }}</a> ({{ statsForum() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/photos">{{ trans('index.photos') }}</a> ({{ statsPhotos() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/blogs">{{ trans('index.blogs') }}</a> ({{ statsBlog() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/boards">{{ trans('index.boards') }}</a> ({{ statsBoard() }})<br>
    <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/adverts">{{ trans('index.advertising') }}</a><br>

    @if (isAdmin('moder'))
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>{{ trans('main.moder') }}</b></div>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/spam">{{ trans('index.complains') }}</a> ({{ statsSpam() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/bans">{{ trans('index.ban_unban') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/banlists">{{ trans('index.banned_list') }}</a> ({{ statsBanned() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/reglists">{{ trans('index.pending_list') }}</a> ({{ statsRegList() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/votes">{{ trans('index.votes') }}</a> ({{ statVotes() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/antimat">{{ trans('index.antimat') }}</a> ({{ statsAntimat() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/banhists">{{ trans('index.ban_history') }}</a> ({{ statsBanHist() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/invitations">{{ trans('index.invitations') }}</a> ({{ statsInvite() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/transfers">{{ trans('index.cash_transactions') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/administrators">{{ trans('index.admins') }}</a> ({{ statsAdmins() }})<br>
    @endif

    @if (isAdmin('admin'))
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>{{ trans('main.admin') }}</b></div>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/rules">{{ trans('index.site_rules') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/news">{{ trans('index.news') }}</a> ({{ statsNews() }})<br>

        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/ipbans">{{ trans('index.ip_ban') }}</a> ({{ statsIpBanned() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/phpinfo">{{ trans('index.phpinfo') }}</a> ({{ parseVersion(PHP_VERSION) }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/loads">{{ trans('index.loads') }}</a> ({{ statsLoad() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/downs/new">{{ trans('index.new_loads') }}</a> ({{ statsNewLoad() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/errors">{{ trans('index.errors') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/blacklists">{{ trans('index.blacklist') }}</a> ({{ statsBlacklist() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/offers">{{ trans('index.offers') }}</a> ({{ statsOffers() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/stickers">{{ trans('index.stickers') }}</a> ({{ statsStickers() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/status">{{ trans('index.user_statuses') }}</a><br>
    @endif

    @if (isAdmin('boss'))
        <div class="b"><i class="fa fa-cog fa-lg text-muted"></i> <b>{{ trans('main.boss') }}</b></div>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/settings">{{ trans('index.site_settings') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/users">{{ trans('index.users') }}</a> ({{ statsUsers() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/caches">{{ trans('index.cache_clear') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/backups">{{ trans('index.backup') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/checkers">{{ trans('index.site_scan') }}</a> ({{ statsChecker() }})<br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/delivery">{{ trans('index.private_mailing') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/logs">{{ trans('index.logs_visits') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/notices">{{ trans('index.email_templates') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/files">{{ trans('index.pages_editing') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/delusers">{{ trans('index.user_cleaning') }}</a><br>
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/modules">{{ trans('index.modules') }}</a><br>
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
