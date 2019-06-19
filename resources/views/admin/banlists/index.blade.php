@extends('layout')

@section('title')
    {{ trans('index.banned_list') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.banned_list') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isNotEmpty())
        @foreach ($users as $user)
            <div class="b">
                {!! $user->getGender() !!} <b>{!! $user->getProfile() !!}</b>

                @if ($user->lastBan->created_at)
                    ({{ dateFixed($user->lastBan->created_at) }})
                @endif
            </div>

            <div>
                {{ trans('users.ending_ban') }}: {{ formatTime($user->timeban - SITETIME) }}<br>

                @if ($user->lastBan->id)
                    {{ trans('users.banned') }}: <b>{!! $user->lastBan->sendUser->getProfile() !!}</b><br>
                    {{ trans('users.reason_ban') }}: {!! bbCode($user->lastBan->reason) !!}<br>
                @endif

                <i class="fa fa-pencil-alt"></i> <a href="/admin/bans/edit?user={{ $user->login }}">{{ trans('main.edit') }}</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        {{ trans('main.total_users') }}: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError(trans('main.empty_users')) !!}
    @endif
@stop
