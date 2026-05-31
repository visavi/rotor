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
            <div class="alert alert-warning my-3">
                <i class="fa fa-database"></i> {{ __('install.migrations_pending', ['count' => count($pendingMigrations)]) }}
                <ul class="mb-0 mt-2">
                    @foreach ($pendingMigrations as $migration)
                        <li><code>{{ $migration }}</code></li>
                    @endforeach
                </ul>
            </div>

            <button id="migrate-btn" class="btn btn-primary mb-3" onclick="runMigrations()"
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

        <div id="migrate-output" class="mb-3 d-none"><pre class="prettyprint p-3"></pre></div>

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">&copy; 2005-{{ date('Y') }} VISAVI.NET</p>
        </footer>
    </div>

    <script>
    function runMigrations() {
        const btn = document.getElementById('migrate-btn');
        const outputWrap = document.getElementById('migrate-output');
        const output = outputWrap.querySelector('pre');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + btn.dataset.running;
        outputWrap.classList.remove('d-none');

        function runNext() {
            fetch(btn.dataset.next, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    output.innerHTML += '<span class="text-danger">' + data.error + '</span>\n';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-play"></i> ' + btn.dataset.done;
                    return;
                }

                if (data.migration) {
                    const text = data.output || data.migration;
                    output.innerHTML += '<span class="text-success">✓</span> ' + text.replace(/\n/g, ' ') + '\n';
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
                    output.innerHTML += '\n<strong>' + allDone + '</strong>';
                } else {
                    runNext();
                }
            })
            .catch(() => {
                output.innerHTML += '<span class="text-danger">' + btn.dataset.error + '</span>\n';
                btn.disabled = false;
            });
        }

        runNext();
    }
    </script>
@stop
