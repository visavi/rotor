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
        <div class="section-title"><i class="fa fa-cog fa-lg"></i> {{ __('main.editor') }}</div>
        <div class="section-body">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-2">
                @hook('adminBlockEditor')
            </div>
        </div>
    </div>

    @if (isAdmin('moder'))
        <div class="section mb-3 shadow">
            <div class="section-title"><i class="fa fa-cog fa-lg"></i> {{ __('main.moder') }}</div>
            <div class="section-body">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-2">
                    <div class="col">
                        <a href="/admin/spam" class="app-tile">
                            <div class="app-tile-icon" style="background:#dc3545"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="app-tile-label">{{ __('index.complains') }}<span class="badge bg-adaptive app-tile-badge">{{ statsSpam() }}</span></div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/bans" class="app-tile">
                            <div class="app-tile-icon" style="background:#fd7e14"><i class="fas fa-ban"></i></div>
                            <div class="app-tile-label">{{ __('index.ban_unban') }}</div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/banlists" class="app-tile">
                            <div class="app-tile-icon" style="background:#d63384"><i class="fas fa-list"></i></div>
                            <div class="app-tile-label">{{ __('index.banned_list') }}<span class="badge bg-adaptive app-tile-badge">{{ statsBanned() }}</span></div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/reglists" class="app-tile">
                            <div class="app-tile-icon" style="background:#20c997"><i class="fas fa-user-clock"></i></div>
                            <div class="app-tile-label">{{ __('index.pending_list') }}<span class="badge bg-adaptive app-tile-badge">{{ statsRegList() }}</span></div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/antimat" class="app-tile">
                            <div class="app-tile-icon" style="background:#6f42c1"><i class="fas fa-filter"></i></div>
                            <div class="app-tile-label">{{ __('index.antimat') }}<span class="badge bg-adaptive app-tile-badge">{{ statsAntimat() }}</span></div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/banhists" class="app-tile">
                            <div class="app-tile-icon" style="background:#6c757d"><i class="fas fa-history"></i></div>
                            <div class="app-tile-label">{{ __('index.ban_history') }}<span class="badge bg-adaptive app-tile-badge">{{ statsBanHist() }}</span></div>
                        </a>
                    </div>
                    @hook('adminBlockModer')
                </div>
            </div>
        </div>
    @endif

    @if (isAdmin('admin'))
        <div class="section mb-3 shadow">
            <div class="section-title"><i class="fa fa-cog fa-lg"></i> {{ __('main.admin') }}</div>
            <div class="section-body">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-2">
                    <div class="col">
                        <a href="/admin/rules" class="app-tile">
                            <div class="app-tile-icon" style="background:#0d6efd"><i class="fas fa-gavel"></i></div>
                            <div class="app-tile-label">{{ __('index.site_rules') }}</div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/ipbans" class="app-tile">
                            <div class="app-tile-icon" style="background:#dc3545"><i class="fas fa-shield-alt"></i></div>
                            <div class="app-tile-label">{{ __('index.ip_ban') }}<span class="badge bg-adaptive app-tile-badge">{{ statsIpBanned() }}</span></div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/phpinfo" class="app-tile">
                            <div class="app-tile-icon" style="background:#6c757d"><i class="fas fa-info-circle"></i></div>
                            <div class="app-tile-label">{{ __('index.phpinfo') }}<span class="badge bg-adaptive app-tile-badge">{{ parseVersion(PHP_VERSION) }}</span></div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/errors" class="app-tile">
                            <div class="app-tile-icon" style="background:#fd7e14"><i class="fas fa-bug"></i></div>
                            <div class="app-tile-label">{{ __('index.errors') }}</div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/blacklists" class="app-tile">
                            <div class="app-tile-icon" style="background:#212529"><i class="fas fa-minus-circle"></i></div>
                            <div class="app-tile-label">{{ __('index.blacklist') }}<span class="badge bg-adaptive app-tile-badge">{{ statsBlacklist() }}</span></div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/stickers" class="app-tile">
                            <div class="app-tile-icon" style="background:#ffc107"><i class="fas fa-smile"></i></div>
                            <div class="app-tile-label">{{ __('index.stickers') }}<span class="badge bg-adaptive app-tile-badge">{{ statsStickers() }}</span></div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/status" class="app-tile">
                            <div class="app-tile-icon" style="background:#20c997"><i class="fas fa-user-tag"></i></div>
                            <div class="app-tile-label">{{ __('index.user_statuses') }}</div>
                        </a>
                    </div>
                    @hook('adminBlockAdmin')
                </div>
            </div>
        </div>
    @endif

    @if (isAdmin('boss'))
        <div class="section mb-3 shadow">
            <div class="section-title"><i class="fa fa-cog fa-lg"></i> {{ __('main.boss') }}</div>
            <div class="section-body">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-2">
                    <div class="col">
                        <a href="/admin/modules" class="app-tile">
                            <div class="app-tile-icon" style="background:#6f42c1"><i class="fas fa-puzzle-piece"></i></div>
                            <div class="app-tile-label">{{ __('index.modules') }}</div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/settings" class="app-tile">
                            <div class="app-tile-icon" style="background:#0d6efd"><i class="fas fa-sliders-h"></i></div>
                            <div class="app-tile-label">{{ __('index.site_settings') }}</div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/users" class="app-tile">
                            <div class="app-tile-icon" style="background:#20c997"><i class="fas fa-users"></i></div>
                            <div class="app-tile-label">{{ __('index.users') }}<span class="badge bg-adaptive app-tile-badge">{{ statsUsers() }}</span></div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/caches" class="app-tile">
                            <div class="app-tile-icon" style="background:#198754"><i class="fas fa-broom"></i></div>
                            <div class="app-tile-label">{{ __('index.cache_clear') }}</div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/logs" class="app-tile">
                            <div class="app-tile-icon" style="background:#6c757d"><i class="fas fa-list-alt"></i></div>
                            <div class="app-tile-label">{{ __('index.logs_visits') }}</div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/notices" class="app-tile">
                            <div class="app-tile-icon" style="background:#d63384"><i class="fas fa-envelope"></i></div>
                            <div class="app-tile-label">{{ __('index.email_templates') }}</div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/user-fields" class="app-tile">
                            <div class="app-tile-icon" style="background:#fd7e14"><i class="fas fa-user-edit"></i></div>
                            <div class="app-tile-label">{{ __('index.user_fields') }}</div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/search" class="app-tile">
                            <div class="app-tile-icon" style="background:#de00de"><i class="fas fa-search"></i></div>
                            <div class="app-tile-label">{{ __('index.search') }}</div>
                        </a>
                    </div>
                    @hook('adminBlockBoss')
                </div>
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
