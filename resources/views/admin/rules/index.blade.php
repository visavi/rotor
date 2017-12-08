@extends('layout')

@section('title')
    Правила сайта
@stop

@section('content')

    <h1>Правила сайта</h1>

    @if ($rules)
        <div>
            {!! bbCode($rules->text) !!}
            <hr>

            Последнее изменение: {{ dateFixed($rules->created_at) }}
        </div>
        <br>
    @else
        {!! showError('Правила сайта еще не установлены!') !!}
    @endif

    <i class="fa fa-pencil-alt"></i> <a href="/admin/rules/edit">Редактировать</a><br>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
