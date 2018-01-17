@extends('layout')

@section('title')
    Мои ключи
@stop

@section('content')

    <h1>Мои ключи</h1>

    @if ($keys->isNotEmpty())
        Всего ключей: {{ $keys->count() }}<br>
        <textarea rows="10">{{ $keys->implode('hash', ',') }}</textarea><br><br>
    @else
        {!! showError('У вас нет пригласительных ключей!') !!}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/invitations">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
