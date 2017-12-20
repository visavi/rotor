@extends('layout')

@section('title')
    PHP-info
@stop

@section('content')

    <h1>PHP-info</h1>

    PHP version: <b>{{ phpversion() }}</b><br>

    @if (zend_version())
        Zend version: <b>{{ zend_version() }}</b><br>
    @endif

    @if ($gdInfo)
        GD Version: <b>{{ $gdInfo }}</b><br>
    @endif

    PDO MySQL: <b>{{ $pdoVersion }}</b><br><br>

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
