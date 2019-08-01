@extends('layout')

@section('title')
    {{ trans('admin.invitations.my_keys') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/invitations">{{ trans('index.invitations') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('admin.invitations.my_keys') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($keys->isNotEmpty())
        {{ trans('main.total') }}: {{ $keys->count() }}<br>
        <textarea rows="10">{{ $keys->implode('hash', ',') }}</textarea><br><br>
    @else
        {!! showError(trans('admin.invitations.empty_keys')) !!}
    @endif
@stop
