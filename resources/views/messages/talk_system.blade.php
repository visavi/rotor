@extends('layout')

@section('title')
    {{ trans('messages.notifications') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('main.menu') }}</a></li>
            <li class="breadcrumb-item"><a href="/messages">{{ trans('messages.private_messages') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('messages.notifications') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($messages->isNotEmpty())

        @foreach ($messages as $data)
            <div class="post">
                <div class="b">
                    <div class="img">
                        {!! $user->getAvatar() !!}
                    </div>

                    <div class="text-muted float-right">
                        {{  dateFixed($data->created_at) }}
                    </div>

                    <b>{{ trans('messages.system') }}</b>

                    @unless ($data->reading)
                        <br><span class="badge badge-info">{{ trans('messages.new') }}</span>
                    @endunless
                </div>
                <div class="message">{!! bbCode($data->text) !!}</div>
            </div>
        @endforeach

        {!! pagination($page) !!}

    @else
        {!! showError(trans('messages.empty_notifications')) !!}
    @endif

    {{ trans('messages.total_notifications') }}: <b>{{ $page->total }}</b><br><br>

    @if ($page->total)
        <i class="fa fa-times"></i> <a href="/messages/delete/0?token={{ $_SESSION['token'] }}">{{ trans('messages.delete_talk') }}</a><br>
    @endif
    <i class="fa fa-search"></i> <a href="/searchusers">{{ trans('index.user_search') }}</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">{{ trans('index.contacts') }}</a> / <a href="/ignores">{{ trans('index.ignores') }}</a><br>
@stop
