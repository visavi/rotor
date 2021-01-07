@extends('layout')

@section('title', __('index.logs_visits'))

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
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {!! $log->user->getAvatar() !!}
                    {!! $log->user->getOnline() !!}
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        {!! $log->user->getProfile() !!}
                        <small class="section-date text-muted font-italic">{{  dateFixed($log->created_at) }}</small>
                    </div>
                </div>

                <div class="section-body border-top">
                    {{ __('admin.logs.page') }}: {{ $log->request }}<br>
                    {{ __('admin.logs.referer') }}: {{ $log->referer }}<br>
                    <div class="small text-muted font-italic mt-2">
                        {{ $log->brow }}, {{ $log->ip }}
                    </div>
                </div>
            </div>
        @endforeach

        {{ $logs->links() }}

        <i class="fa fa-times"></i> <a href="/admin/logs/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('admin.logs.confirm_clear') }}')">{{ __('main.clear') }}</a><br>
    @else
        {!! showError(__('admin.logs.empty_logs')) !!}
    @endif
@stop
