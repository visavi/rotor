@extends('layout')

@section('title')
    Блокнот
@stop

@section('content')

    <h1>Блокнот</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">Мое меню</a></li>
            <li class="breadcrumb-item active">Блокнот</li>
        </ol>
    </nav>

    Здесь вы можете хранить отрывки сообщений или любую другую важную информацию<br><br>

    @if ($note->text)
        <div>Личная запись:<br>
            {!! bbCode($note->text) !!}
        </div>
        <br>

        Последнее изменение: {{ dateFixed($note->created_at) }}<br><br>
    @else
        {!! showError('Запись пустая или отсутствует!') !!}
    @endif

    <i class="fa fa-pencil-alt"></i> <a href="/notebook/edit">Редактировать</a><br>
@stop
