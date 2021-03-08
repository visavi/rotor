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

        <div class="form-group mb-3">
            <textarea class="form-control" rows="10">{{ $keys->implode('hash', ',') }}</textarea>
        </div>
    @else
        {{ showError(__('admin.invitations.empty_keys')) }}
    @endif
@stop
