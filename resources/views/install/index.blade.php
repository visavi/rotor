@extends('layout_simple')

@section('title', (setting('app_installed') ? __('install.update') : __('install.install')) . ' Rotor')

@section('content')
    <div class="container border px-5" style="background: var(--bs-body-bg);">
        <div class="py-5 text-center">
            <a href="/"><img class="d-block mx-auto mb-3" src="/assets/img/images/logo_big.png" alt=""></a>
            <h2>Mobile CMS</h2>
        </div>

        <div class="row">
            <div class="col-12 mb-3">
                <form method="get" class="row col-3">
                    <label for="language" class="form-label">Выберите язык - Select language:</label>
                    <div class="input-group mb-3">
                        <select class="form-select" name="lang" id="language">
                            @foreach ($languages as $language)
                                <?php $selected = ($language === $lang) ? ' selected' : ''; ?>
                                <option value="{{ $language }}"{{ $selected }}>{{ $language }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary">{{ __('main.select') }}</button>
                    </div>
                </form>

                <h1>{{ __('install.step1') }}</h1>

                <div class="alert alert-info">
                    {{__('install.debug') }}
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('install.env') }}</h5>
                                <p class="card-text">
                                    @foreach ($keys as $key)
                                        {{ $key }} - {{ trim(var_export(env($key), true), "'") }}<br>
                                    @endforeach
                                </p>
                                <span class="text-success fst-italic">{{ __('install.app_key') }}</span>
                            </div>
                        </div>
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

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('install.check_requirements') }}</h5>
                                <p class="card-text">
                                    <?php $errors['critical']['php'] = version_compare(PHP_VERSION, $versions['php']) >= 0 ?>
                                    <span class="{{ $errors['critical']['php'] ? 'text-success' : 'text-danger' }}">PHP: {{ parseVersion(PHP_VERSION) }}</span><br>

                                    <?php $errors['critical']['pdo_mysql'] = extension_loaded('pdo_mysql') ?>
                                    <?php $version = strtok(\App\Http\Controllers\InstallController::getModuleSetting('pdo_mysql', ['Client API version', 'PDO Driver for MySQL, client library version']), '-'); ?>
                                    <span class="{{ $errors['critical']['pdo_mysql'] ? 'text-success' : 'text-danger' }}">PDO-MySQL: {{ $version }}</span><br>
                                    <?php

                                    $dbVersion = DB::select('SELECT VERSION() as version')[0]->version ?? 'N/A';
                                    ?>
                                    <span class="{{ version_compare($dbVersion, $versions['mysql']) >= 0 ? 'text-success' : 'text-danger' }}">MySQL: {{ $dbVersion }}</span><br>

                                    <?php $errors['simple']['bcmath'] = extension_loaded('bcmath') ?>
                                    <span class="{{ $errors['simple']['bcmath'] ? 'text-success' : 'text-danger' }}">BCMath</span><br>

                                    <?php $errors['simple']['ctype'] = extension_loaded('ctype') ?>
                                    <span class="{{ $errors['simple']['ctype'] ? 'text-success' : 'text-danger' }}">Ctype</span><br>

                                    <?php $errors['simple']['json'] = extension_loaded('json') ?>
                                    <span class="{{ $errors['simple']['json'] ? 'text-success' : 'text-danger' }}">Json</span><br>

                                    <?php $errors['simple']['tokenizer'] = extension_loaded('tokenizer') ?>
                                    <span class="{{ $errors['simple']['tokenizer'] ? 'text-success' : 'text-danger' }}">Tokenizer</span><br>

                                    <?php $errors['simple']['fileinfo'] = extension_loaded('fileinfo') ?>
                                    <span class="{{ $errors['simple']['fileinfo'] ? 'text-success' : 'text-danger' }}">Fileinfo</span><br>

                                    <?php $errors['simple']['mbstring'] = extension_loaded('mbstring') ?>
                                    <?php $version = \App\Http\Controllers\InstallController::getModuleSetting('mbstring', ['oniguruma version', 'Multibyte regex (oniguruma) version']); ?>
                                    <span class="{{ $errors['simple']['mbstring'] ? 'text-success' : 'text-danger' }}">MbString: <?= $version ?></span><br>

                                    <?php $errors['simple']['openssl'] = extension_loaded('openssl') ?>
                                    <?php $version = \App\Http\Controllers\InstallController::getModuleSetting('openssl', ['OpenSSL Library Version', 'OpenSSL Header Version']); ?>
                                    <span class="{{ $errors['simple']['openssl'] ? 'text-success' : 'text-danger' }}">OpenSSL: {{ $version }}</span><br>

                                    <?php $errors['simple']['xml'] = extension_loaded('xml') ?>
                                    <?php $version = \App\Http\Controllers\InstallController::getModuleSetting('xml', ['libxml2 Version']); ?>
                                    <span class="{{ $errors['simple']['xml'] ? 'text-success' : 'text-danger' }}">XML: {{ $version }}</span><br>

                                    <?php $errors['simple']['gd'] = extension_loaded('gd') ?>
                                    <?php $version = \App\Http\Controllers\InstallController::getModuleSetting('gd', ['GD headers Version', 'GD library Version']); ?>
                                    <span class="{{ $errors['simple']['gd'] ? 'text-success' : 'text-danger' }}">GD: {{ $version }}</span><br>

                                    <?php $errors['simple']['curl'] = extension_loaded('curl') ?>
                                    <?php $version = \App\Http\Controllers\InstallController::getModuleSetting('curl', ['Curl Information', 'cURL Information']); ?>
                                    <span class="{{ $errors['simple']['curl'] ? 'text-success' : 'text-danger' }}">Curl: {{ $version }}</span><br>
                                    <span class="fst-italic my-3">
                                {{ __('install.ffmpeg') }}
                            </span>
                                </p>
                            </div>
                        </div>

                        @if (config('app.url') !== url('/'))
                            <div class="alert alert-danger">
                                {{ __('install.requirements_url', ['env_url' => config('app.url'), 'current_url' => url('/')]) }}
                            </div>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('install.chmod_rights') }}</h5>
                                <p class="card-text">
                                    @foreach ($dirs as $dir)
                                        <?php $chmod = decoct(fileperms($dir)) % 1000; ?>
                                        <?php $errors['chmod'][$dir] = is_writable($dir); ?>

                                        <span class="{{ $errors['chmod'][$dir] ? 'text-success' : 'text-danger' }}">{{ str_replace(base_path(), '', $dir) }}: {{ $chmod }}</span><br>
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{ __('install.chmod_views') }}><br><br>

                {{ __('install.chmod') }}<br>
                {{ __('install.errors') }}<br><br>

                @if (! in_array(false, $errors['critical'], true) && ! in_array(false, $errors['chmod'], true))
                    <div class="alert alert-success">
                        {{ __('install.continue') }}
                    </div>

                    @if (! in_array(false, $errors['simple'], true))
                        {{ __('install.requirements_pass') }}<br><br>
                    @else
                        <div class="alert alert-warning">
                            {{ __('install.requirements_warning') }}<br>
                            {{ __('install.requirements_not_pass') }}<br>
                            {{ __('install.continue_restrict') }}
                        </div>
                    @endif

                    <a class="btn btn-primary" style="font-size: 18px" href="/install/status?lang={{ $lang }}">{{ __('install.check_status') }}</a>
                    <span class="text-info fw-bold">{{ setting('app_installed') ? __('install.update') : __('install.install') }}</span>
                @else
                    <div class="alert alert-danger">
                        {{ __('install.requirements_failed') }}<br>
                        {{ __('install.resolve_errors') }}
                    </div>
                @endif
            </div>
        </div>

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">&copy; 2005-{{ date('Y') }} VISAVI.NET</p>
        </footer>
    </div>
@stop
