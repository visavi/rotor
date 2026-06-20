@extends('layout_simple')

@section('title', $isUpdate ? __('install.step2_update') : __('install.step2_install'))

@section('content')
    <div class="container border px-5">
        <div class="py-5 text-center">
            <a href="/"><img class="d-block mx-auto mb-3" src="/assets/img/images/logo_big.png" alt=""></a>
            <h2>Mobile CMS</h2>
        </div>

        @if ($isUpdate)
            <div class="alert alert-warning fw-bold">
                {{ __('install.update_mode_notice') }}
            </div>
        @endif

        <h1>{{ $isUpdate ? __('install.step2_update') : __('install.step2_install') }}</h1>

        @if (count($pendingMigrations) > 0)
            <div id="pending-alert" class="alert alert-warning my-3">
                <i class="fa fa-database"></i> {{ __('install.migrations_pending', ['count' => count($pendingMigrations)]) }}
                <ul class="mb-0 mt-2">
                    @foreach ($pendingMigrations as $migration)
                        <li><code>{{ $migration }}</code></li>
                    @endforeach
                </ul>
            </div>

            <div id="migrate-output" class="alert alert-secondary d-none"></div>

            <button id="migrate-btn" class="btn btn-primary mb-3" onclick="runMigrations()"
                data-label="{{ __('install.migrations_run', ['count' => count($pendingMigrations)]) }}"
                data-running="{{ __('install.migrations_running') }}"
                data-done="{{ __('install.migrations_done_btn') }}"
                data-all-done="{{ __('install.migrations_all_done') }}"
                data-next="/install/migrate/next?lang={{ $lang }}"
                data-error="{{ __('install.migrations_error') }}">
                <i class="fa fa-play"></i> {{ __('install.migrations_run', ['count' => count($pendingMigrations)]) }}
            </button>
        @else
            <div class="alert alert-success my-3">
                <i class="fa fa-check"></i> {{ __('install.migrations_nothing_pending') }}
            </div>

            @if ($isUpdate)
                <a class="btn btn-primary" href="/">{{ __('install.main_page') }}</a>
            @else
                <a class="btn btn-primary" href="/install/seed?lang={{ $lang }}">{{ __('install.seeds') }}</a>
            @endif
        @endif

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">&copy; 2005-{{ date('Y') }} VISAVI.NET</p>
        </footer>
    </div>

    <script>
    function runMigrations() {
        const btn = document.getElementById('migrate-btn');
        const output = document.getElementById('migrate-output');
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
            fetch(btn.dataset.next, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
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
                    const allDone = btn.dataset.allDone;
                    @if($isUpdate)
                        btn.outerHTML = '<a href="/" class="btn btn-primary mb-3"><i class="fa fa-check"></i> ' + doneLabel + '</a>';
                    @else
                        btn.outerHTML = '<a href="/install/seed?lang={{ $lang }}" class="btn btn-primary mb-3"><i class="fa fa-check"></i> ' + doneLabel + '</a>';
                    @endif
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
@stop
