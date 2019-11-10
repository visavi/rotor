@extends('layout')

@section('title')
    {{ __('index.logs_visits') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.logs_visits') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($logs)
        @foreach ($logs as $log)
            <div class="b">
                <i class="fa fa-file"></i> <b>{!! $log->user->getProfile() !!}</b>
                 ({{  dateFixed($log->created_at) }})
            </div>
            <div>
                {{ __('admin.logs.page') }}: {{ $log->request }}<br>
                {{ __('admin.logs.referer') }}: {{ $log->referer }}<br>
                <small><span class="data">({{ $log->brow }}, {{ $log->ip }})</span></small>
            </div>
        @endforeach

        <br><i class="fa fa-times"></i> <a href="/admin/logs/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('admin.logs.confirm_clear') }}')">{{ __('main.clear') }}</a><br>
    @else
        {!! showError(__('admin.logs.empty_logs')) !!}
    @endif

    {{ $logs->links('app/_paginator') }}
@stop
