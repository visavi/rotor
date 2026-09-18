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
        <i class="fa fa-key fa-lg"></i>
        <b>
            @if(isAdmin('boss'))
                <a href="/admin/upgrade">{{ __('main.version') }} {{ ROTOR_VERSION }}</a>
            @else
                {{ __('main.version') }} {{ ROTOR_VERSION }}
            @endif
        </b>

        @if($hasNewVersion)
            <span class="badge bg-success">{{ __('main.update_available') }}</span>
        @endif
    </div>

    @if ($pendingMigrations > 0)
        <div class="alert alert-warning">
            <i class="fa fa-database"></i>
            <a href="{{ route('admin.upgrade.index') }}#tab-db">{{ __('admin.upgrade.pending', ['count' => $pendingMigrations]) }}</a>
        </div>
    @endif

    @if ($scheduleStalled)
        <div class="alert alert-warning">
            <div><i class="fa fa-clock"></i> <b>{{ __('index.schedule_stalled') }}</b></div>
            <div class="small">
                @if ($lastRun = $scheduleStalled->lastRun())
                    {{ __('index.schedule_last_run', ['date' => dateFixed($lastRun)]) }}
                @else
                    {{ __('index.schedule_never') }}
                @endif
            </div>
            <code class="d-block mt-1 user-select-all">* * * * * php {{ base_path('artisan') }} schedule:run >> /dev/null 2>&1</code>
        </div>
    @endif

    @if ($queuePending > 0)
        <div class="alert alert-warning">
            <div><i class="fa fa-layer-group"></i> <b>{{ __('index.queue_stalled') }}</b></div>
            <div class="small">{{ __('index.queue_pending', ['count' => $queuePending]) }}</div>
        </div>
    @endif

    @if ($mailFailure)
        <div class="alert alert-warning">
            <div><i class="fa fa-envelope"></i> <b>{{ __('index.mail_failed') }}</b></div>
            @if ($mailFailure['time'])
                <div class="small">{{ __('index.mail_failed_time', ['date' => dateFixed($mailFailure['time'])]) }}</div>
            @endif
            <code class="d-block mt-1 user-select-all">{{ $mailFailure['message'] }}</code>
        </div>
    @endif

    @if ($modulesUpdates > 0)
        <div class="alert alert-warning">
            <i class="fas fa-puzzle-piece"></i>
            <a href="{{ route('admin.modules.index') }}">{{ __('admin.modules.updates_available', ['count' => $modulesUpdates]) }}</a>
        </div>
    @endif
    @hook('adminHeader')

    @if ($widgets)
        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-3">
            @foreach ($widgets as $widget)
                <div class="col">
                    <x-stat-tile :widget="$widget" />
                </div>
            @endforeach
        </div>
    @endif

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
                        <a href="{{ route('admin.widgets.index') }}" class="app-tile">
                            <div class="app-tile-icon" style="background:#6610f2"><i class="fas fa-chart-line"></i></div>
                            <div class="app-tile-label">{{ __('index.widgets') }}</div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/users" class="app-tile">
                            <div class="app-tile-icon" style="background:#20c997"><i class="fas fa-users"></i></div>
                            <div class="app-tile-label">{{ __('index.users') }}<span class="badge bg-adaptive app-tile-badge">{{ statsUsers() }}</span></div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/admin/notices" class="app-tile">
                            <div class="app-tile-icon" style="background:#d63384"><i class="fas fa-envelope"></i></div>
                            <div class="app-tile-label">{{ __('index.email_templates') }}</div>
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
