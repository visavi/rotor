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

    @if ($hasNewVersion)
        <div class="alert alert-warning my-3">
            <i class="fa fa-exclamation-triangle"></i> {{ __('admin.upgrade.new_version') }}
        </div>

        <div class="post mb-3">
            <div class="post-message fw-bold">
                <a href="{{ $latestRelease['html_url'] }}">{{ $latestRelease['name'] }}</a>
            </div>

            @if ($latestRelease['body'])
                <div class="post-message">
                    {{ renderHtml(nl2br($latestRelease['body'])) }}
                </div>
            @endif

            <div class="post-author fw-light">
                <span class="avatar-micro">
                    <img class="avatar-default rounded-circle" src="{{ $latestRelease['author']['avatar_url'] }}" alt="Аватар">
                </span>

                <a href="{{ $latestRelease['author']['html_url'] }}">{{ $latestRelease['author']['login'] }}</a>
                <small class="post-date text-body-secondary fst-italic">{{ dateFixed(strtotime($latestRelease['created_at'])) }}</small>
            </div>

            <div>
                @if (isset($latestRelease['assets'][0]))
                    {{ __('admin.upgrade.download') }}: <a href="{{ $latestRelease['assets'][0]['browser_download_url'] }}">{{ $latestRelease['assets'][0]['name'] }}</a> {{ formatSize($latestRelease['assets'][0]['size']) }}
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-success my-3">
            <i class="fa fa-check"></i> {{ __('admin.upgrade.actual_version') }}
        </div>
    @endif

    @if (count($pendingMigrations) > 0)
        <div class="alert alert-warning my-3">
            <i class="fa fa-database"></i> {{ __('admin.upgrade.pending', ['count' => count($pendingMigrations)]) }}
            <ul class="mb-0 mt-2">
                @foreach ($pendingMigrations as $migration)
                    <li><code>{{ $migration }}</code></li>
                @endforeach
            </ul>
        </div>

        <button id="migrate-btn" class="btn btn-warning mb-3" onclick="runMigrations()"
            data-label="{{ __('admin.upgrade.run', ['count' => count($pendingMigrations)]) }}"
            data-running="{{ __('admin.upgrade.running') }}"
            data-done="{{ __('admin.upgrade.done_btn') }}"
            data-all-done="{{ __('admin.upgrade.all_done') }}"
            data-error="{{ __('admin.upgrade.request_error') }}">
            <i class="fa fa-play"></i> {{ __('admin.upgrade.run', ['count' => count($pendingMigrations)]) }}
        </button>
    @else
        <div class="alert alert-success my-3">
            <i class="fa fa-check"></i> {{ __('admin.upgrade.db_actual') }}
        </div>
    @endif

    <div id="migrate-output" class="section mb-3 shadow d-none"></div>

    @if (session('migrateOutput'))
        <div class="section mb-3 shadow">
            {{ renderHtml(nl2br(session('migrateOutput'))) }}
        </div>
    @endif
@stop

@push('scripts')
<script>
function runMigrations() {
    const btn = document.getElementById('migrate-btn');
    const output = document.getElementById('migrate-output');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + btn.dataset.running;
    output.classList.remove('d-none');

    function runNext() {
        fetch('{{ route('admin.upgrade.migrate.next') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                output.innerHTML += '<span class="text-danger">' + data.error + '</span><br>';
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-play"></i> ' + btn.dataset.label;
                return;
            }

            if (data.migration) {
                const text = data.output || data.migration;
                output.innerHTML += '<span class="text-success">✓</span> ' + text.replace(/\n/g, ' ') + '<br>';
                output.scrollTop = output.scrollHeight;
            }

            if (data.done) {
                const doneLabel = btn.dataset.done;
                const allDone = btn.dataset.allDone;
                btn.outerHTML = '<a href="{{ route('admin.upgrade.index') }}" class="btn btn-success mb-3"><i class="fa fa-check"></i> ' + doneLabel + '</a>';
                output.innerHTML += '<br><strong>' + allDone + '</strong>';
            } else {
                runNext();
            }
        })
        .catch(() => {
            output.innerHTML += '<span class="text-danger">' + btn.dataset.error + '</span><br>';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-play"></i> ' + btn.dataset.label;
        });
    }

    runNext();
}
</script>
@endpush
