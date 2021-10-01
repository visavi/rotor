@extends('layout')

@section('title', __('index.phpinfo'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.phpinfo') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <span class="badge bg-success">PHP {{ parseVersion(PHP_VERSION) }}</span>

        @if (zend_version())
            <span class="badge bg-success">Zend {{ parseVersion(zend_version()) }}</span>
        @endif

        @if ($gdInfo)
            <span class="badge bg-success">GD {{ $gdInfo }}</span>
        @endif
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
