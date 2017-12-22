@extends('layout')

@section('title')
    PHP-info
@stop

@section('content')

    <h1>PHP-info</h1>

    <span class="badge badge-success">PHP {{ parseVersion(PHP_VERSION) }}</span>

    @if (zend_version())
        <span class="badge badge-success">Zend {{ parseVersion(zend_version()) }}</span>
    @endif

    @if ($gdInfo)
        <span class="badge badge-success">GD {{ $gdInfo }}</span>
    @endif

    <span class="badge badge-success">MySQL {{ $mysqlVersion }}</span>

    @if ($iniInfo)

        <table class="table table-sm table-striped table-bordered">

            <thead>
                <tr>
                    <th class="w-40">Directive</th>
                    <th class="w-60">Local Value</th>
                </tr>
            </thead>

            @foreach($iniInfo as $inikey => $inivalue)
                <tr><td>{{ $inikey }}</td><td>{{ str_limit($inivalue['local_value'], 50) }}</td></tr>
            @endforeach
        </table><br>

    @else
        {!! showError('Функция ini_get_all запрещена в php.ini') !!}
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
