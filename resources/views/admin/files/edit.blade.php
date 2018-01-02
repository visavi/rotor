@extends('layout')

@section('title')
    {{ 'Редактирование файла '.$path.$file }}
@stop

@section('content')

    <h1>{{ 'Редактирование файла '.$path.$file }}</h1>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
