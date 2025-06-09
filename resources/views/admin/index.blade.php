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
        <i class="fa fa-key fa-lg"></i> <b><a href="/admin/upgrade">{{ __('main.version') }} {{ ROTOR_VERSION }}</a></b>
    </div>
    @hook('adminHeader')

    <div class="section mb-3 shadow">
        <div class="section-title">
            <i class="fa fa-cog fa-lg text-muted"></i> {{ __('main.editor') }}
        </div>
        <div class="section-body">
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/chats">{{ __('index.admin_chat') }}</a> <span class="badge bg-adaptive">{{ statsChat() }}</span><br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('admin.guestbook.index') }}">{{ __('index.guestbook') }}</a> <span class="badge bg-adaptive">{{ statsGuestbook() }}</span><br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('admin.forums.index') }}">{{ __('index.forums') }}</a> <span class="badge bg-adaptive">{{ statsForum() }}</span><br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('admin.photos.index') }}">{{ __('index.photos') }}</a> <span class="badge bg-adaptive">{{ statsPhotos() }}</span><br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('admin.blogs.index') }}">{{ __('index.blogs') }}</a> <span class="badge bg-adaptive">{{ statsBlog() }}</span><br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('admin.boards.index') }}">{{ __('index.boards') }}</a> <span class="badge bg-adaptive">{{ statsBoard() }}</span><br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/admin-adverts">{{ __('index.admin_advertising') }}</a><br>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/adverts">{{ __('index.advertising') }}</a><br>
            @hook('adminBlockEditor')
        </div>
    </div>

    @if (isAdmin('moder'))
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-cog fa-lg text-muted"></i> {{ __('main.moder') }}
            </div>
            <div class="section-body">
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/spam">{{ __('index.complains') }}</a> <span class="badge bg-adaptive">{{ statsSpam() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/bans">{{ __('index.ban_unban') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/banlists">{{ __('index.banned_list') }}</a> <span class="badge bg-adaptive">{{ statsBanned() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/reglists">{{ __('index.pending_list') }}</a> <span class="badge bg-adaptive">{{ statsRegList() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/votes">{{ __('index.votes') }}</a> <span class="badge bg-adaptive">{{ statVotes() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/antimat">{{ __('index.antimat') }}</a> <span class="badge bg-adaptive">{{ statsAntimat() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/banhists">{{ __('index.ban_history') }}</a> <span class="badge bg-adaptive">{{ statsBanHist() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/invitations">{{ __('index.invitations') }}</a> <span class="badge bg-adaptive">{{ statsInvite() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/transfers">{{ __('index.cash_transactions') }}</a><br>
                @hook('adminBlockModer')
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
                <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('admin.news.index') }}">{{ __('index.news') }}</a> <span class="badge bg-adaptive">{{ statsNews() }}</span><br>

                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/ipbans">{{ __('index.ip_ban') }}</a> <span class="badge bg-adaptive">{{ statsIpBanned() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/phpinfo">{{ __('index.phpinfo') }}</a> <span class="badge bg-adaptive">{{ parseVersion(PHP_VERSION) }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('admin.loads.index') }}">{{ __('index.loads') }}</a> <span class="badge bg-adaptive">{{ statsLoad() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('admin.downs.new') }}">{{ __('index.new_loads') }}</a> <span class="badge bg-adaptive">{{ statsNewLoad() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/errors">{{ __('index.errors') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/blacklists">{{ __('index.blacklist') }}</a> <span class="badge bg-adaptive">{{ statsBlacklist() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('admin.offers.index') }}">{{ __('index.offers') }}</a> <span class="badge bg-adaptive">{{ statsOffers() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/stickers">{{ __('index.stickers') }}</a> <span class="badge bg-adaptive">{{ statsStickers() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/status">{{ __('index.user_statuses') }}</a><br>
                @hook('adminBlockAdmin')
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
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/users">{{ __('index.users') }}</a> <span class="badge bg-adaptive">{{ statsUsers() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/caches">{{ __('index.cache_clear') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/backups">{{ __('index.backup') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/checkers">{{ __('index.site_scan') }}</a> <span class="badge bg-adaptive">{{ statsChecker() }}</span><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/delivery">{{ __('index.private_mailing') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/logs">{{ __('index.logs_visits') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/notices">{{ __('index.email_templates') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/files">{{ __('index.page_editor') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/delusers">{{ __('index.user_cleaning') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/paid-adverts">{{ __('index.paid_adverts') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/user-fields">{{ __('index.user_fields') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/search">{{ __('index.search') }}</a><br>
                <i class="far fa-circle fa-lg text-muted"></i> <a href="/admin/modules">{{ __('index.modules') }}</a><br>
                @hook('adminBlockBoss')
            </div>
        </div>
    @endif

    @if (! $existBoss)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> {!! __('admin.not_exists_boss') !!}
        </div>
    @endif

    @if (file_exists(app_path('Http/Controllers/InstallController.php')))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> {!! __('admin.exists_install') !!}
        </div>
    @endif
    @hook('adminFooter')
@stop
