@extends('layout')

@section('title')
    Бан пользователя {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/bans">Бан / Разбан</a></li>
            <li class="breadcrumb-item active">Бан пользователя {{ $user->login }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{!! $user->getGender() !!} {!! $user->getProfile() !!}</h3>

    @if ($user->lastBan->id)
        Последний бан: {{ dateFixed($user->lastBan->created_at) }}<br>
        Забанил: <b>{!! $user->lastBan->sendUser->getProfile() !!}</b><br>
        Срок: {{ formatTime($user->lastBan->term) }}<br>
        Причина: {!! bbCode($user->lastBan->reason) !!}<br>
    @endif

    <i class="fa fa-history"></i> <b><a href="/admin/banhists/view?user={{ $user->login }}">История банов</a></b><br><br>

    @if ($user->level === 'banned' && $user->timeban > SITETIME)
        <div class="form">
            <b><span style="color:#ff0000">Внимание, данный пользователь заблокирован!</span></b><br>
            До окончания бана: {{ formatTime($user->timeban - SITETIME) }}<br>
        </div>

        <i class="fa fa-pencil-alt"></i> <a href="/admin/bans/change?user={{ $user->login }}">Изменить</a><br>
        <i class="fa fa-check-circle"></i> <a href="/admin/bans/unban?user={{ $user->login }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите разбанить пользователя?')">Разбанить</a><hr>
    @else
        <div class="form">
            <form method="post" action="/admin/bans/edit?user={{ $user->login }}">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <div class="form-group{{ hasError('time') }}">
                    <label for="time">Время бана:</label>
                    <input class="form-control" name="time" id="time" value="{{ getInput('time') }}" required>
                    {!! textError('time') !!}
                </div>

                <?php $inputType = getInput('type'); ?>
                <div class="form-group{{ hasError('type') }}">
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="inputTypeMinutes" name="type" value="minutes"{{ $inputType === 'minutes' ? ' checked' : '' }}>
                        <label class="custom-control-label" for="inputTypeMinutes">Минут</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="inputTypeHours" name="type" value="hours"{{ $inputType === 'hours' ? ' checked' : '' }}>
                        <label class="custom-control-label" for="inputTypeHours">Часов</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="inputTypeDays" name="type" value="days"{{ $inputType === 'days' ? ' checked' : '' }}>
                        <label class="custom-control-label" for="inputTypeDays">Дней</label>
                    </div>
                    {!! textError('type') !!}
                </div>

                <div class="form-group{{ hasError('reason') }}">
                    <label for="reason">Причина бана:</label>
                    <textarea class="form-control markItUp" id="reason" rows="5" name="reason" required>{{ getInput('reason') }}</textarea>
                    {!! textError('reason') !!}
                </div>

                <div class="form-group{{ hasError('note') }}">
                    <label for="notice">Заметка:</label>
                    <textarea class="form-control markItUp" id="notice" rows="5" name="notice">{{ getInput('notice', $user->note->text) }}</textarea>
                    {!! textError('notice') !!}
                </div>

                <button class="btn btn-primary">Забанить</button>
            </form>
        </div><br>

        <p class="text-muted font-italic">Внимание! Постарайтесь как можно подробнее описать причину бана</p>
    @endif
@stop
