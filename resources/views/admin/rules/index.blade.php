@extends('layout')

@section('title')
    Правила сайта
@stop

@section('content')

    <div class="float-right">
        <a class="btn btn-success" href="/admin/rules/edit">Редактировать</a>
    </div><br>

    <h1>Правила сайта</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Правила сайта</li>
        </ol>
    </nav>

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
@stop
