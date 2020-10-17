@extends('layout')

@section('title', __('admin.invitations.my_keys'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/invitations">{{ __('index.invitations') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.invitations.my_keys') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($keys->isNotEmpty())
        {{ __('main.total') }}: {{ $keys->count() }}<br>
        <textarea rows="10">{{ $keys->implode('hash', ',') }}</textarea><br><br>
    @else
        {!! showError(__('admin.invitations.empty_keys')) !!}
    @endif
@stop
