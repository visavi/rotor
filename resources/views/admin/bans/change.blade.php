@extends('layout')

@section('title', __('admin.bans.change_ban') . ' ' . $user->getName())

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/bans">{{ __('index.ban_unban') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.bans.change_ban') }} {{ $user->getName() }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{!! $user->getGender() !!} {!! $user->getProfile() !!}</h3>

    @if ($user->lastBan->id)
        {{ __('users.last_ban') }}: {{ dateFixed($user->lastBan->created_at) }}<br>
        {{ __('users.banned') }}: <b>{!! $user->lastBan->sendUser->getProfile() !!}</b><br>
        {{ __('users.term') }}: {{ formatTime($user->lastBan->term) }}<br>
        {{ __('users.reason_ban') }}: {!! bbCode($user->lastBan->reason) !!}<br>
    @endif

    {{ __('users.ending_ban') }}: {{ formatTime($user->timeban - SITETIME) }}<br>

    <div class="section-form p-3 shadow">
        <form method="post" action="/admin/bans/change?user={{ $user->login }}">
            @csrf
            <div class="form-group{{ hasError('timeban') }}">
                <label for="timeban">{{ __('admin.bans.time_ban') }}:</label>
                <input class="form-control" type="datetime-local" name="timeban" id="timeban" value="{{ getInput('timeban', dateFixed($user->timeban, 'Y-m-d\TH:i')) }}" required>
                <div class="invalid-feedback">{{ textError('timeban') }}</div>
            </div>

            <div class="form-group{{ hasError('reason') }}">
                <label for="reason">{{ __('users.reason_ban') }}:</label>
                <textarea class="form-control markItUp" id="reason" rows="5" name="reason" required>{{ getInput('reason', $user->lastBan->reason) }}</textarea>
                <div class="invalid-feedback">{{ textError('reason') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
