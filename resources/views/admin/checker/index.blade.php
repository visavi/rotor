@extends('layout')

@section('title')
    Сканирование сайта
@stop

@section('content')

    <h1>Сканирование сайта</h1>

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
        Внимание сервис не учитывает некоторые расширения файлов: {{ setting('nocheck') }}
    </p>

    <p><a class="btn btn-primary" href="/admin/checker/scan?token={{ $_SESSION['token'] }}"><i class="fa fa-sync"></i> Сканировать</a></p>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
