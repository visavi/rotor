@extends('layout')

@section('title')
    Сканирование сайта
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('common.panel') }}</a></li>
            <li class="breadcrumb-item active">Сканирование сайта</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($diff)
        <b><span style="color:#ff0000">Новые файлы и новые параметры файлов:</span></b><br><br>

        @if ($diff['left'])
            @foreach($diff['left'] as $file)
                <i class="fa fa-plus-circle text-success"></i> {{ $file }}<br>
            @endforeach
            <br>
        @else
            {!! showError('Нет новых изменений!') !!}
        @endif

        <b><span style="color:#ff0000">Удаленные файлы и старые параметры файлов:</span></b><br><br>

        @if ($diff['right'])
            @foreach($diff['right'] as $file)
                <i class="fa fa-minus-circle text-danger"></i> {{ $file }}<br>
            @endforeach
            <br>
        @else
            {!! showError('Нет старых изменений!') !!}
        @endif

    @else
        {!! showError('Необходимо провести начальное сканирование!') !!}
    @endif

    <p class="text-muted font-italic">
        Сканирование системы позволяет узнать какие файлы или папки менялись в течение определенного времени<br>
        Внимание, сервис не учитывает некоторые расширения файлов: {{ setting('nocheck') }}
    </p>

    <p><a class="btn btn-primary" href="/admin/checkers/scan?token={{ $_SESSION['token'] }}"><i class="fa fa-sync"></i> Сканировать</a></p>
@stop
