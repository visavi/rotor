@extends('layout')

@section('title')
    Блокнот - @parent
@stop

@section('content')

    <h1>Блокнот</h1>

    Здесь вы можете хранить отрывки сообщений или любую другую важную информацию<br><br>

    @if ($note->text)
        <div>Личная запись:<br>
            {!! bbCode($note->text) !!}
        </div>
        <br>

        Последнее изменение: {{ dateFixed($note->created_at) }}<br><br>
    @else
        {{ showError('Запись пустая или отсутствует!') }}
    @endif

    <i class="fa fa-pencil"></i> <a href="/notebook/edit">Редактировать</a><br>
@stop
