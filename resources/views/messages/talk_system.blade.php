@extends('layout')

@section('title')
    {{ __('messages.notifications') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item"><a href="/messages">{{ __('index.messages') }}</a></li>
            <li class="breadcrumb-item active">{{ __('messages.notifications') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($messages->isNotEmpty())
        @foreach ($messages as $data)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    <img class="avatar-default rounded-circle" src="/assets/img/images/avatar_system.png" alt="">
                    <div class="user-status bg-success" title="Online"></div>
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        <b>{{ __('messages.system') }}</b>

                        @unless ($data->reading)
                            <span class="badge badge-info">{{ __('messages.new') }}</span>
                        @endunless
                    </div>

                    <div class="section-date text-muted font-italic small">
                        {{  dateFixed($data->created_at) }}
                    </div>
                </div>

                <div class="section-body border-top my-1 py-1">
                    <div class="section-message">
                        {!! bbCode($data->text) !!}
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('messages.empty_notifications')) !!}
    @endif

    {{ $messages->links() }}

    <br>{{ __('main.total') }}: <b>{{ $messages->total() }}</b><br>

    @if ($messages->total())
        <i class="fa fa-times"></i> <a href="/messages/delete/0?token={{ $_SESSION['token'] }}">{{ __('messages.delete_talk') }}</a><br>
    @endif
    <i class="fa fa-search"></i> <a href="/searchusers">{{ __('index.user_search') }}</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">{{ __('index.contacts') }}</a> / <a href="/ignores">{{ __('index.ignores') }}</a><br>
@stop
