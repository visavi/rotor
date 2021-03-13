@extends('layout')

@section('title', __('index.errors'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.errors') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (empty(setting('errorlog')))
        <span class="text-danger">{{ __('admin.errors.hint') }}</span><br>
    @endif

    <div class="mb-3">
        @foreach ($lists as $key => $value)
            <a class="badge badge-{{ $key === $code ? 'success' : 'light' }}" href="/admin/errors?code={{ $key }}">{{ $value }}</a>
        @endforeach
    </div>

    @if ($logs->isNotEmpty())
        @foreach ($logs as $data)
            <div class="section mb-3 shadow">
                <span class="section-title">{{ $data->request }}</span>
                <small class="section-date text-muted font-italic">{{ dateFixed($data->created_at) }}</small>

                <div class="section-body border-top">
                    Referer: {{ $data->referer ?: __('main.undefined') }}<br>
                    {{ __('main.user') }}: {{ $data->user->exists ? $data->user->getProfile() : setting('guestsuser') }}
                    <div class="small text-muted font-italic mt-2">{{ $data->brow }}, {{ $data->ip }}</div>
                </div>
            </div>
        @endforeach

        {{ $logs->links() }}

        {{ __('main.total') }}: <b>{{ $logs->total() }}</b><br>

        @if (isAdmin('boss'))
            <i class="fa fa-trash-alt"></i> <a href="/admin/errors/clear?token={{ $_SESSION['token'] }}">{{ __('main.clear') }}</a><br>
        @endif
    @else
        {{ showError(__('main.empty_records')) }}
    @endif
@stop
