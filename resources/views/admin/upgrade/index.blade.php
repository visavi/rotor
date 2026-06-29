@extends('layout')

@section('title', __('index.upgrade'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.upgrade') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{{ __('admin.upgrade.site_version', ['version' => ROTOR_VERSION]) }}</h3>

    <ul class="nav nav-tabs mt-3" id="upgradeTabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-core">
                {{ __('admin.upgrade.core_updates') }}
                @if (count($newReleases) > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ count($newReleases) }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-db">
                {{ __('admin.upgrade.db_tab') }}
                @if (count($pendingMigrations) > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ count($pendingMigrations) }}</span>
                @endif
            </button>
        </li>
        @if ($reinstall)
            <li class="nav-item ms-auto">
                <button class="nav-link text-body-secondary" data-bs-toggle="tab" data-bs-target="#tab-reinstall">
                    <i class="fa fa-rotate-right"></i> {{ __('admin.upgrade.reinstall_tab') }}
                </button>
            </li>
        @endif
    </ul>

    <div class="tab-content border border-top-0 p-4 mb-3">

        {{-- Вкладка: обновление ядра --}}
        <div class="tab-pane fade show active" id="tab-core">

            @if (count($permErrors) > 0)
                <div class="alert alert-danger">
                    <i class="fa fa-lock"></i> {{ __('admin.upgrade.perm_error', ['dirs' => implode(', ', $permErrors)]) }}
                </div>
            @endif

            @if (count($newReleases) > 0)
                @foreach ($newReleases as $release)
                    @php $asset = $release['asset'] ?? null; @endphp

                    <div class="post mb-3">
                        <div class="post-message fw-bold">
                            <a href="{{ $release['html_url'] }}">{{ $release['name'] }}</a>
                        </div>

                        @if ($release['body'])
                            <div class="post-message markdown-body">
                                {{ renderMarkdown($release['body']) }}
                            </div>
                        @endif

                        <div class="post-author fw-light">
                            <span class="avatar-micro">
                                <img class="avatar-default rounded-circle" src="{{ $release['author']['avatar_url'] }}" alt="">
                            </span>
                            <a href="{{ $release['author']['html_url'] }}">{{ $release['author']['login'] }}</a>
                            <small class="post-date text-body-secondary fst-italic">{{ dateFixed(\Illuminate\Support\Facades\Date::parse($release['created_at'])) }}</small>
                        </div>

                        @if ($asset && count($permErrors) === 0)
                            <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
                                @include('admin/upgrade/_update-button', [
                                    'tag'   => $release['tag_name'],
                                    'label' => __('admin.upgrade.update_download', ['size' => formatSize($asset['size'])]),
                                    'badge' => $release['is_upgrade'] ? 'upgrade' : 'full',
                                ])

                                @if ($release['full_asset'])
                                    @include('admin/upgrade/_update-button', [
                                        'tag'   => $release['tag_name'],
                                        'label' => __('admin.upgrade.download_full', ['size' => formatSize($release['full_asset']['size'])]),
                                        'full'  => true,
                                        'class' => 'btn-sm btn-outline-secondary',
                                    ])
                                @endif
                            </div>
                        @elseif ($asset)
                            <div class="mt-2">
                                {{ __('admin.upgrade.download') }}: <a href="{{ $asset['browser_download_url'] }}">{{ $asset['name'] }}</a> {{ formatSize($asset['size']) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="alert alert-success mb-0">
                    <i class="fa fa-check"></i> {{ __('admin.upgrade.actual_version') }}
                </div>
            @endif

            <div id="update-output" class="section mt-3 shadow d-none"></div>
        </div>

        {{-- Вкладка: миграции БД --}}
        <div class="tab-pane fade" id="tab-db">

            @if (count($pendingMigrations) > 0)
                <div id="pending-alert" class="alert alert-warning">
                    <i class="fa fa-database"></i> {{ __('admin.upgrade.pending', ['count' => count($pendingMigrations)]) }}
                    <ul class="mb-0 mt-2">
                        @foreach ($pendingMigrations as $migration)
                            <li><code>{{ $migration }}</code></li>
                        @endforeach
                    </ul>
                </div>

                <div id="migrate-output" class="alert alert-secondary d-none"></div>

                <button id="migrate-btn" class="btn btn-warning" onclick="runMigrations()"
                    data-label="{{ __('admin.upgrade.run', ['count' => count($pendingMigrations)]) }}"
                    data-running="{{ __('admin.upgrade.running') }}"
                    data-done="{{ __('admin.upgrade.done_btn') }}"
                    data-all-done="{{ __('admin.upgrade.all_done') }}"
                    data-error="{{ __('admin.upgrade.request_error') }}">
                    <i class="fa fa-play"></i> {{ __('admin.upgrade.run', ['count' => count($pendingMigrations)]) }}
                </button>
            @else
                <div class="alert alert-success mb-0">
                    <i class="fa fa-check"></i> {{ __('admin.upgrade.db_actual') }}
                </div>
            @endif
        </div>

        {{-- Вкладка: переустановка текущей версии --}}
        @if ($reinstall)
            <div class="tab-pane fade" id="tab-reinstall">
                <p class="text-body-secondary">{{ __('admin.upgrade.reinstall_hint') }}</p>

                @if (count($permErrors) > 0)
                    <div class="alert alert-danger">
                        <i class="fa fa-lock"></i> {{ __('admin.upgrade.perm_error', ['dirs' => implode(', ', $permErrors)]) }}
                    </div>
                @else
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @include('admin/upgrade/_update-button', [
                            'tag'     => $reinstall['tag'],
                            'label'   => __('admin.upgrade.reinstall', ['size' => formatSize($reinstall['asset']['size'])]),
                            'class'   => 'btn-outline-secondary',
                            'confirm' => __('admin.upgrade.reinstall_confirm'),
                        ])

                        @if ($reinstall['full_asset'])
                            @include('admin/upgrade/_update-button', [
                                'tag'     => $reinstall['tag'],
                                'label'   => __('admin.upgrade.download_full', ['size' => formatSize($reinstall['full_asset']['size'])]),
                                'full'    => true,
                                'class'   => 'btn-outline-secondary',
                                'confirm' => __('admin.upgrade.reinstall_confirm'),
                            ])
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
@stop

@push('scripts')
<script>
function runUpdate(btn) {
    if (! confirm(btn.dataset.confirm)) {
        return;
    }

    const tag         = btn.dataset.tag;
    const full        = btn.dataset.full;
    const downloadUrl = btn.dataset.downloadUrl;
    const applyUrl    = btn.dataset.applyUrl;
    const output      = document.getElementById('update-output');

    // На странице может быть несколько кнопок (upgrade/полная/переустановка) —
    // блокируем все, чтобы не запустить параллельно
    document.querySelectorAll('.btn-update').forEach(b => b.disabled = true);
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + btn.dataset.labelProgress;
    btn.parentNode.after(output);
    output.classList.remove('d-none');
    output.innerHTML = '';

    fetch(downloadUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ tag, full }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            output.innerHTML = '<span class="text-danger">' + data.error + '</span>';
            document.querySelectorAll('.btn-update').forEach(b => b.disabled = false);
            return;
        }

        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + btn.dataset.labelApplying;

        return fetch(applyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ tag }),
        });
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            output.innerHTML = '<span class="text-danger">' + data.error + '</span>';
            document.querySelectorAll('.btn-update').forEach(b => b.disabled = false);
            return;
        }

        const labelDone   = btn.dataset.labelDone;
        const labelReload = btn.dataset.labelReload;

        let html = '<span class="text-success"><i class="fa fa-check"></i> ' + labelDone + '</span>';

        if (data.errors && data.errors.length > 0) {
            html += '<br><span class="text-warning">' + data.errors.join('<br>') + '</span>';
        }

        output.innerHTML = html;
        btn.outerHTML = '<a href="{{ route('admin.upgrade.index') }}" class="btn btn-success mt-2"><i class="fa fa-refresh"></i> ' + labelReload + '</a>';
    })
    .catch(() => {
        output.innerHTML = '<span class="text-danger">' + btn.dataset.labelError + '</span>';
        document.querySelectorAll('.btn-update').forEach(b => b.disabled = false);
    });
}

function runMigrations() {
    const btn     = document.getElementById('migrate-btn');
    const output  = document.getElementById('migrate-output');
    const pending = document.getElementById('pending-alert');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + btn.dataset.running;
    if (pending) pending.classList.add('d-none');
    output.classList.remove('d-none');

    function stopWithError(message) {
        output.innerHTML += '<span class="text-danger">' + message + '</span><br>';
        output.scrollTop = output.scrollHeight;
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-play"></i> ' + btn.dataset.label;
    }

    function runNext() {
        fetch('{{ route('admin.upgrade.migrate.next') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(async r => {
            const data = await r.json().catch(() => ({}));

            if (! r.ok || data.error) {
                throw new Error(data.error || data.message || btn.dataset.error);
            }

            return data;
        })
        .then(data => {
            if (data.migration) {
                const text = data.output || data.migration;
                output.innerHTML += '<span class="text-success">✓</span> ' + text.replace(/\n/g, ' ') + '<br>';
                output.scrollTop = output.scrollHeight;
            }

            if (data.done) {
                const doneLabel = btn.dataset.done;
                const allDone   = btn.dataset.allDone;
                btn.outerHTML = '<a href="{{ route('admin.upgrade.index') }}" class="btn btn-success"><i class="fa fa-check"></i> ' + doneLabel + '</a>';
                output.innerHTML += '<br><strong>' + allDone + '</strong>';
            } else if (data.migration) {
                runNext();
            } else {
                stopWithError(btn.dataset.error);
            }
        })
        .catch(err => {
            stopWithError(err.message || btn.dataset.error);
        });
    }

    runNext();
}
</script>
@endpush
