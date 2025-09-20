@extends('layout')

@section('title', __('index.phpinfo'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.phpinfo') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <h5>Environment Info</h5>
        <span class="badge bg-primary">OS {{ PHP_OS_FAMILY }}</span>
        <span class="badge bg-primary">PHP {{ parseVersion(PHP_VERSION) }}</span>

    @if ($gdInfo)
            <span class="badge bg-primary">GD {{ $gdInfo }}</span>
        @endif

        <span class="badge bg-primary">MySQL {{ $dbVersion }}</span>
        <br>

        <span class="badge bg-primary">Laravel {{ app()->version() }}</span>
        <span class="badge {{ config('app.debug') ? 'bg-warning' : 'bg-success' }}">
            Debug: {{ config('app.debug') ? 'false' : 'true' }}
        </span>
        <span class="badge {{ config('app.env') === 'production' ? 'bg-success' : 'bg-secondary' }} ">
            Env: {{ config('app.env') }}
        </span>

        <span class="badge bg-primary">Memory Limit: {{ ini_get('memory_limit') }}</span>
        <span class="badge bg-primary">Upload Max: {{ ini_get('upload_max_filesize') }}</span>
        <span class="badge bg-primary">Timezone: {{ date_default_timezone_get() }}</span>
    </div>

    @if ($iniInfo)
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered">
                <thead>
                <tr>
                    <th class="w-40">Directive</th>
                    <th class="w-60">Local Value</th>
                </tr>
                </thead>

                @foreach ($iniInfo as $inikey => $inivalue)
                    <tr>
                        <td>{{ $inikey }}</td>
                        <td>{{ truncateString(trim(var_export($inivalue['local_value'], true), "'"), 50) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>

    @else
        {{ showError('Функция ini_get_all запрещена в php.ini') }}
    @endif
@stop
