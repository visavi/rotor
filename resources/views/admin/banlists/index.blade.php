@extends('layout')

@section('title', __('index.banned_list'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.banned_list') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isNotEmpty())
        @foreach ($users as $user)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {{ $user->getAvatar() }}
                    {{ $user->getOnline() }}
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        {{ $user->getGender() }} {{ $user->getProfile() }}

                        @if ($user->lastBan->created_at)
                            <small class="section-date text-muted font-italic">{{ dateFixed($user->lastBan->created_at) }}</small>
                        @endif
                    </div>

                    <div class="text-end">
                         <a href="/admin/bans/edit?user={{ $user->login }}"><i class="fa fa-pencil-alt"></i></a>
                    </div>
                </div>

                <div class="section-body border-top">
                    {{ __('users.ending_ban') }}: {{ formatTime($user->timeban - SITETIME) }}<br>

                    @if ($user->lastBan->id)
                        {{ __('users.banned') }}: {{ $user->lastBan->sendUser->getProfile() }}<br>
                        {{ __('users.reason_ban') }}: {{ bbCode($user->lastBan->reason) }}<br>
                    @endif
                </div>
            </div>
        @endforeach

        <br>{{ __('main.total_users') }}: <b>{{ $users->total() }}</b><br>

    @else
        {{ showError(__('main.empty_users')) }}
    @endif

    {{ $users->links() }}
@stop
