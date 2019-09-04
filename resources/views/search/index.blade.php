@extends('layout')

@section('title')
    {{ __('index.search') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.search') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {!! __('search.help_text') !!}<br>

    {{ __('search.help_link') }}:<br>
    <a href="https://site.yandex.ru">Yandex search</a><br>
    <a href="https://cse.google.ru">Google search</a><br>
@stop
