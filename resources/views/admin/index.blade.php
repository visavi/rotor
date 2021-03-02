@extends('layout')

@section('title', __('index.panel'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.panel') }}</li>
        </ol>
    </nav>
@stop

@section('content')

    <div class="mb-3">
        <i class="fa fa-key fa-lg"></i> <b><a href="/admin/upgrade">{{ __('main.version') }} {{ VERSION }}</a></b>
    </div>

    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="fa fa-cog fa-lg text-muted"></i> {{ __('main.editor') }}
        </div>
        <div class="section-body">
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/chats">{{ __('index.admin_chat') }}</a> ({{ statsChat() }})<br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/guestbook">{{ __('index.guestbook') }}</a> ({{ statsGuestbook() }})<br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/forums">{{ __('index.forums') }}</a> ({{ statsForum() }})<br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/photos">{{ __('index.photos') }}</a> ({{ statsPhotos() }})<br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/blogs">{{ __('index.blogs') }}</a> ({{ statsBlog() }})<br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/boards">{{ __('index.boards') }}</a> ({{ statsBoard() }})<br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/admin-adverts">{{ __('index.admin_advertising') }}</a><br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/adverts">{{ __('index.advertising') }}</a><br>
        </div>
    </div>

    @if (isAdmin('moder'))
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-cog fa-lg text-muted"></i> {{ __('main.moder') }}
            </div>
            <div class="section-body">
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/spam">{{ __('index.complains') }}</a> ({{ statsSpam() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/bans">{{ __('index.ban_unban') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/banlists">{{ __('index.banned_list') }}</a> ({{ statsBanned() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/reglists">{{ __('index.pending_list') }}</a> ({{ statsRegList() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/votes">{{ __('index.votes') }}</a> ({{ statVotes() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/antimat">{{ __('index.antimat') }}</a> ({{ statsAntimat() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/banhists">{{ __('index.ban_history') }}</a> ({{ statsBanHist() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/invitations">{{ __('index.invitations') }}</a> ({{ statsInvite() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/transfers">{{ __('index.cash_transactions') }}</a><br>
            </div>
        </div>
    @endif

    @if (isAdmin('admin'))
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-cog fa-lg text-muted"></i> {{ __('main.admin') }}
            </div>
            <div class="section-body">
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/rules">{{ __('index.site_rules') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/news">{{ __('index.news') }}</a> ({{ statsNews() }})<br>

                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/ipbans">{{ __('index.ip_ban') }}</a> ({{ statsIpBanned() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/phpinfo">{{ __('index.phpinfo') }}</a> ({{ parseVersion(PHP_VERSION) }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/loads">{{ __('index.loads') }}</a> ({{ statsLoad() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/downs/new">{{ __('index.new_loads') }}</a> ({{ statsNewLoad() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/errors">{{ __('index.errors') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/blacklists">{{ __('index.blacklist') }}</a> ({{ statsBlacklist() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/offers">{{ __('index.offers') }}</a> ({{ statsOffers() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/stickers">{{ __('index.stickers') }}</a> ({{ statsStickers() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/status">{{ __('index.user_statuses') }}</a><br>
            </div>
        </div>
    @endif

    @if (isAdmin('boss'))
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-cog fa-lg text-muted"></i> {{ __('main.boss') }}
            </div>
            <div class="section-body">
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/settings">{{ __('index.site_settings') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/users">{{ __('index.users') }}</a> ({{ statsUsers() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/caches">{{ __('index.cache_clear') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/backups">{{ __('index.backup') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/checkers">{{ __('index.site_scan') }}</a> ({{ statsChecker() }})<br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/delivery">{{ __('index.private_mailing') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/logs">{{ __('index.logs_visits') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/notices">{{ __('index.email_templates') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/files">{{ __('index.page_editor') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/delusers">{{ __('index.user_cleaning') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/paid-adverts">{{ __('index.paid_adverts') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/modules">{{ __('index.modules') }}</a><br>
            </div>
        </div>
    @endif

    @if (! $existBoss)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            Внимание! Отсутствует профиль суперадмина<br>
            В базе данных не найден пользователь с правами boss
        </div>
    @endif

    @if (file_exists(HOME . '/install'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            Внимание! Необходимо удалить директорию install<br>
            Наличие этой директории может нарушить безопасность сайта. Удалите ее прямо сейчас!
        </div>
    @endif
@stop
