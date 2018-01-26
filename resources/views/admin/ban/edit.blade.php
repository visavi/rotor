@extends('layout')

@section('title')
    Редактирование пользователя {{ $user->login }}
@stop

@section('content')

    <h1>Редактирование пользователя {{ $user->login }}</h1>

    <h3>{!! $user->getGender() !!} {!! profile($user) !!}</h3>

    @if ($user->lastBan->id)
        Срок: {{ formatTime($user->lastBan->term) }}<br>
        Забанил: <b>{!! profile($user->lastBan->sendUser) !!}</b><br>
        Причина: {!! bbCode($user->lastBan->reason) !!}<br>
    @endif

    Строгих нарушений: <b>{{ $user->totalban }}</b><br>
    <i class="fa fa-history"></i> <b><a href="/admin/banhist?user={{ $user->login }}">История банов</a></b><br><br>

    @if ($user->level === 'banned' && $user->timeban > SITETIME)
        <div class="form">
            <b><span style="color:#ff0000">Внимание, данный пользователь заблокирован!</span></b><br>
            До окончания бана осталось: {{ formatTime($user->timeban - SITETIME) }}<br>
        </div>


        <i class="fa fa-pencil-alt"></i> <a href="/admin/ban?act=editban&amp;user={{ $user->login }}">Изменить</a><br>
        <i class="fa fa-arrow-circle-up"></i> <a href="/admin/ban?act=razban&amp;user={{ $user->login }}&amp;token={{ $_SESSION['token'] }}">Разбанить</a><hr>
    @else

        @if ($user->totalban < 5)
            <div class="form">
                <form method="post" action="/admin/ban?act=zaban&amp;user={{ $user->login }}">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                    <div class="form-group{{ hasError('time') }}">
                        <label for="time">Время бана:</label>
                        <input class="form-control" name="time" id="time" value="{{ getInput('time') }}" required>
                        {!! textError('time') !!}
                    </div>

                    <?php $inputType = getInput('type'); ?>
                    <div class="form-group{{ hasError('type') }}">
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="inputTypeMins" name="type" value="minutes"{{ $inputType === 'minutes' ? ' checked' : '' }}>
                            <label class="custom-control-label" for="inputTypeMins">Минут</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="inputTypeHours" name="type" value="hours"{{ $inputType === 'hours' ? ' checked' : '' }}>
                            <label class="custom-control-label" for="inputTypeHours">Часов</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="inputTypeDays" name="type" value="days"{{ $inputType === 'days' ? ' checked' : '' }}>
                            <label class="custom-control-label" for="inputTypeDays">Часов</label>
                        </div>
                        {!! textError('type') !!}
                    </div>

                    <div class="form-group{{ hasError('reason') }}">
                        <label for="markItUp">Причина бана:</label>
                        <textarea class="form-control" id="markItUp" rows="5" name="reason" required>{{ getInput('reason') }}</textarea>
                        {!! textError('reason') !!}
                    </div>

                    <div class="form-group{{ hasError('note') }}">
                        <label for="markItUp">Заметка:</label>
                        <textarea class="form-control" id="markItUp" rows="5" name="note" required>{{ getInput('note', $note) }}</textarea>
                        {!! textError('note') !!}
                    </div>

                    <button class="btn btn-primary">Забанить</button>
                </form>
            </div><br>

            Подсчет нарушений производится при бане более чем на 12 часов<br>
            При общем числе нарушений более пяти, профиль пользователя удаляется<br>
            Максимальное время бана {{ round(setting('maxbantime') / 1440) }} суток<br>
            Внимание! Постарайтесь как можно подробнее описать причину бана<br><br>
        @else
            <b><span style="color:#ff0000">Внимание! Пользователь превысил лимит банов</span></b><br>
            Вы можете удалить этот профиль!<br><br>
            <i class="fa fa-times"></i> <b><a href="/admin/ban?act=deluser&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">Удалить профиль</a></b><br><br>
        @endif
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/ban">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
