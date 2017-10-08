@extends('layout')

@section('title')
    Вас забанили
@stop

@section('content')

    <h1>Вас забанили</h1>

    @if ($banhist)
        <b><span style="color:#ff0000">Причина бана: {!! bbCode($banhist->reason) !!}</span></b><br><br>

        @if (setting('addbansend') == 1 && $banhist->explain == 1)
            <div class="form">
                <form method="post" action="/ban">
                    Объяснение:<br>
                    <textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br>
                    <button class="btn btn-primary">Отправить</button>
                </form>
            </div><br>

            Если модер вас забанил по ошибке или вы считаете, что бан не заслужен, то вы можете написать объяснение своего нарушения<br>
            В случае если ваше объяснение будет рассмотрено и удовлетворено, то возможно вас и разбанят<br><br>
        @endif
    @endif

    До окончания бана осталось <b>{{ formatTime($user->timeban - SITETIME) }}</b><br><br>

    Чтобы не терять время зря, рекомендуем вам ознакомиться с <b><a href="/rules">Правилами сайта</a></b><br><br>

    Общее число строгих нарушений: <b>{{ $user->totalban }}</b><br>
    Внимание, максимальное количество нарушений: <b>5</b><br>
    При превышении лимита нарушений ваш профиль автоматически удаляется<br>
    Востановление профиля или данных после этого будет невозможным<br>
    Будьте внимательны, старайтесь не нарушать больше правил<br><br>

@stop
