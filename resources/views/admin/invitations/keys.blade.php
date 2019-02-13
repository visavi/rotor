@extends('layout')

@section('title')
    Мои ключи
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('common.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/invitations">Приглашения</a></li>
            <li class="breadcrumb-item active">Мои ключи</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($keys->isNotEmpty())
        Всего ключей: {{ $keys->count() }}<br>
        <textarea rows="10">{{ $keys->implode('hash', ',') }}</textarea><br><br>
    @else
        {!! showError('У вас нет пригласительных ключей!') !!}
    @endif
@stop
