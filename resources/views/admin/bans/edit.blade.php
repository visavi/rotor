@extends('layout')

@section('title', trans('admin.bans.user_ban') . ' ' . $user->login)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/bans">{{ trans('index.ban_unban') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('admin.bans.user_ban') }} {{ $user->login }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{!! $user->getGender() !!} {!! $user->getProfile() !!}</h3>

    @if ($user->lastBan->id)
        {{ trans('users.last_ban') }}: {{ dateFixed($user->lastBan->created_at) }}<br>
        {{ trans('users.banned') }}: <b>{!! $user->lastBan->sendUser->getProfile() !!}</b><br>
        {{ trans('users.term') }}: {{ formatTime($user->lastBan->term) }}<br>
        {{ trans('users.reason_ban') }}: {!! bbCode($user->lastBan->reason) !!}<br>
    @endif

    <i class="fa fa-history"></i> <b><a href="/admin/banhists/view?user={{ $user->login }}">{{ trans('index.ban_history') }}</a></b><br><br>

    @if ($user->level === 'banned' && $user->timeban > SITETIME)
        <div class="form">
            <b><span style="color:#ff0000">{{ trans('users.user_banned') }}</span></b><br>
            {{ trans('users.ending_ban') }}: {{ formatTime($user->timeban - SITETIME) }}<br>
        </div>

        <i class="fa fa-pencil-alt"></i> <a href="/admin/bans/change?user={{ $user->login }}">{{ trans('main.change') }}</a><br>
        <i class="fa fa-check-circle"></i> <a href="/admin/bans/unban?user={{ $user->login }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('admin.bans.confirm_unban') }}')">{{ trans('users.unban') }}</a><hr>
    @else
        <div class="form">
            <form method="post" action="/admin/bans/edit?user={{ $user->login }}">
                @csrf
                <div class="form-group{{ hasError('time') }}">
                    <label for="time">{{ trans('admin.bans.time_ban') }}:</label>
                    <input class="form-control" name="time" id="time" value="{{ getInput('time') }}" required>
                    <div class="invalid-feedback">{{ textError('time') }}</div>
                </div>

                <?php $inputType = getInput('type'); ?>
                <div class="form-group{{ hasError('type') }}">
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="inputTypeMinutes" name="type" value="minutes"{{ $inputType === 'minutes' ? ' checked' : '' }}>
                        <label class="custom-control-label" for="inputTypeMinutes">{{ trans('main.minutes') }}</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="inputTypeHours" name="type" value="hours"{{ $inputType === 'hours' ? ' checked' : '' }}>
                        <label class="custom-control-label" for="inputTypeHours">{{ trans('main.hours') }}</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="inputTypeDays" name="type" value="days"{{ $inputType === 'days' ? ' checked' : '' }}>
                        <label class="custom-control-label" for="inputTypeDays">{{ trans('main.days') }}</label>
                    </div>
                    <div class="invalid-feedback">{{ textError('type') }}</div>
                </div>

                <div class="form-group{{ hasError('reason') }}">
                    <label for="reason">{{ trans('users.reason_ban') }}:</label>
                    <textarea class="form-control markItUp" id="reason" rows="5" name="reason" required>{{ getInput('reason') }}</textarea>
                    <div class="invalid-feedback">{{ textError('reason') }}</div>
                </div>

                <div class="form-group{{ hasError('note') }}">
                    <label for="notice">{{ trans('main.note') }}:</label>
                    <textarea class="form-control markItUp" id="notice" rows="5" name="notice">{{ getInput('notice', $user->note->text) }}</textarea>
                    <div class="invalid-feedback">{{ textError('notice') }}</div>
                </div>

                <button class="btn btn-primary">{{ trans('admin.bans.banned') }}</button>
            </form>
        </div><br>

        <p class="text-muted font-italic">{{ trans('admin.bans.ban_hint') }}</p>
    @endif
@stop
