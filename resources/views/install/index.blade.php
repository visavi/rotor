@extends('layout_simple')

@section('title', ($isUpdate ? __('install.update') : __('install.install')) . ' Rotor')

@section('content')
    <div class="container border rounded-3 shadow-sm px-3 px-md-5 my-4" style="max-width: 960px; background: var(--bs-tertiary-bg);">
        <div class="py-4 py-md-5 text-center">
            <a href="/"><img class="d-block mx-auto mb-3" src="/assets/img/images/logo_big.png" alt=""></a>
            <h2 class="mb-1">Mobile CMS</h2>
            <p class="text-secondary mb-0">{{ $isUpdate ? __('install.update') : __('install.install') }} Rotor</p>
        </div>

        @if ($isUpdate)
            <div class="alert alert-warning fw-bold">
                {{ __('install.update_mode_notice') }}
            </div>
        @endif

        <div class="row">
            <div class="col-12 mb-3">
                <form method="get" class="mb-4" style="max-width: 340px;">
                    <label for="language" class="form-label small text-secondary">Выберите язык — Select language</label>
                    <div class="input-group">
                        <select class="form-select" name="lang" id="language">
                            @foreach ($languages as $language)
                                <?php $selected = ($language === $lang) ? ' selected' : ''; ?>
                                <option value="{{ $language }}"{{ $selected }}>{{ $language }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-primary">{{ __('main.select') }}</button>
                    </div>
                </form>

                <h1>{{ __('install.step1') }}</h1>

                <div class="alert alert-info">
                    {{__('install.debug') }}
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-semibold">{{ __('install.env') }}</div>
                    <div class="card-body">
                        <dl class="row mb-2 small">
                            @foreach ($keys as $key)
                                <dt class="col-sm-4 col-lg-3 fw-normal text-secondary">{{ $key }}</dt>
                                <dd class="col-sm-8 col-lg-9 mb-1"><code>{{ trim(var_export(env($key), true), "'") }}</code></dd>
                            @endforeach
                        </dl>
                        <span class="text-success fst-italic small">{{ __('install.app_key') }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    {{ __('install.requirements', [
                        'php' => $versions['php'],
                        'mysql' => $versions['mysql'],
                        'maria' => $versions['maria'],
                        'pgsql' => $versions['pgsql'],
                   ]) }}
                </div>

                @php
                    $okBadge   = '<span class="badge rounded-pill bg-success"><i class="fa-solid fa-check"></i></span>';
                    $failBadge = '<span class="badge rounded-pill bg-danger"><i class="fa-solid fa-xmark"></i></span>';
                    $warnBadge = '<span class="badge rounded-pill bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation"></i></span>';
                @endphp

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header fw-semibold">{{ __('install.check_requirements') }}</div>
                            <ul class="list-group list-group-flush">
                                <?php $errors['critical']['php'] = version_compare(PHP_VERSION, $versions['php']) >= 0 ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>PHP <span class="text-secondary small ms-1">{{ parseVersion(PHP_VERSION) }}</span></span>
                                    {!! $errors['critical']['php'] ? $okBadge : $failBadge !!}
                                </li>

                                <?php $errors['critical']['pdo'] = $database['pdoLoaded'] ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>PDO <span class="text-secondary small ms-1">{{ $database['pdoExt'] }} {{ $database['client'] }}</span></span>
                                    {!! $errors['critical']['pdo'] ? $okBadge : $failBadge !!}
                                </li>

                                <?php $errors['critical']['db'] = $database['connected'] ?>
                                @php
                                    $dbBadge = ! $database['connected'] ? $failBadge : ($database['versionOk'] ? $okBadge : $warnBadge);
                                @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>DB <span class="text-secondary small ms-1">{{ $database['driver'] }} {{ $database['version'] }}</span></span>
                                    {!! $dbBadge !!}
                                </li>

                                <?php
                                $extensions = [
                                    'bcmath'    => ['BCMath', null],
                                    'ctype'     => ['Ctype', null],
                                    'json'      => ['Json', null],
                                    'tokenizer' => ['Tokenizer', null],
                                    'fileinfo'  => ['Fileinfo', null],
                                    'mbstring'  => ['MbString', extension_loaded('mbstring') ? phpversion('mbstring') : null],
                                    'openssl'   => ['OpenSSL', extension_loaded('openssl') ? OPENSSL_VERSION_TEXT : null],
                                    'xml'       => ['XML', extension_loaded('xml') ? LIBXML_DOTTED_VERSION : null],
                                    'gd'        => ['GD', extension_loaded('gd') ? gd_info()['GD Version'] : null],
                                    'curl'      => ['Curl', extension_loaded('curl') ? curl_version()['version'] : null],
                                ];
                                ?>
                                @foreach ($extensions as $ext => [$label, $ver])
                                    <?php $errors['simple'][$ext] = extension_loaded($ext) ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $label }}@if ($ver)<span class="text-secondary small ms-1">{{ $ver }}</span>@endif</span>
                                        {!! $errors['simple'][$ext] ? $okBadge : $failBadge !!}
                                    </li>
                                @endforeach

                                <?php
                                $optional = [
                                    'zip'     => ['Zip', extension_loaded('zip')],
                                    'symlink' => ['Symlink', function_exists('symlink')],
                                ];
                                ?>
                                @foreach ($optional as $key => [$label, $ok])
                                    <?php $errors['simple'][$key] = $ok ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $label }}@unless ($ok)<span class="text-secondary small ms-1">{{ __('install.modules_required') }}</span>@endunless</span>
                                        {!! $ok ? $okBadge : $warnBadge !!}
                                    </li>
                                @endforeach
                            </ul>
                            <div class="card-footer fst-italic small text-secondary">{{ __('install.ffmpeg') }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header fw-semibold">{{ __('install.chmod_rights') }}</div>
                            <ul class="list-group list-group-flush">
                                @foreach ($dirs as $dir)
                                    <?php $chmod = decoct(fileperms($dir)) % 1000; ?>
                                    <?php $errors['chmod'][$dir] = is_writable($dir); ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-break small">{{ str_replace(base_path(), '', $dir) }}</span>
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="text-secondary small font-monospace">{{ $chmod }}</span>
                                            {!! $errors['chmod'][$dir] ? $okBadge : $failBadge !!}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                @if ($database['pdoLoaded'] && in_array($database['driver'], ['mysql', 'mariadb']) && ! $database['mysqlnd'])
                    <div class="alert alert-warning">{{ __('install.mysqlnd_notice') }}</div>
                @endif

                @unless ($database['connected'])
                    <div class="alert alert-danger">{{ __('install.db_connection_failed') }}: {{ $database['error'] }}</div>
                @endunless

                @if (config('app.url') !== url('/'))
                    <div class="alert alert-danger">
                        {{ __('install.requirements_url', ['env_url' => config('app.url'), 'current_url' => url('/')]) }}
                    </div>
                @endif

                <p class="text-secondary small">
                    {{ __('install.chmod_views') }}<br>
                    {{ __('install.chmod') }}<br>
                    {{ __('install.errors') }}
                </p>

                @if (! in_array(false, $errors['critical'], true) && ! in_array(false, $errors['chmod'], true))
                    <div class="alert alert-success d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>{{ __('install.continue') }}</span>
                    </div>

                    @if (! in_array(false, $errors['simple'], true))
                        <p class="text-success">{{ __('install.requirements_pass') }}</p>
                    @else
                        <div class="alert alert-warning">
                            {{ __('install.requirements_warning') }}<br>
                            {{ __('install.requirements_not_pass') }}<br>
                            {{ __('install.continue_restrict') }}
                        </div>
                    @endif

                    <a class="btn btn-lg {{ $isUpdate ? 'btn-warning' : 'btn-primary' }}" href="/install/status?lang={{ $lang }}">
                        {{ __('install.check_status') }}
                        <span class="badge {{ $isUpdate ? 'bg-dark' : 'bg-light text-dark' }} ms-1">{{ $isUpdate ? __('install.update') : __('install.install') }}</span>
                    </a>
                @else
                    <div class="alert alert-danger d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-xmark"></i>
                        <span>{{ __('install.requirements_failed') }} {{ __('install.resolve_errors') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">&copy; 2005-{{ date('Y') }} VISAVI.NET</p>
        </footer>
    </div>
@stop
