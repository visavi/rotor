@extends('layout')

@section('title')
    Вас забанили
@stop

@section('content')
    @if ($banhist)
        <b><span style="color:#ff0000">Причина бана: {!! bbCode($banhist->reason) !!}</span></b><br><br>

        @if ($banhist->explain && setting('addbansend'))
            <div class="form">
                <form method="post" action="/ban">

                  <div class="form-group{{ hasError('msg') }}">
                      <label for="msg">Объяснение:</label>
                      <textarea class="form-control markItUp" id="msg" rows="5" name="msg" required>{{ getInput('msg') }}</textarea>
                      {!! textError('msg') !!}
                  </div>

                    <button class="btn btn-primary">Отправить</button>
                </form>
            </div><br>

            Если модер вас забанил по ошибке или вы считаете, что бан не заслужен, то вы можете написать объяснение своего нарушения<br>
            В случае если ваше объяснение будет рассмотрено и удовлетворено, то возможно вас и разбанят<br><br>
        @endif
    @endif

    До окончания бана: <b>{{ formatTime($user->timeban - SITETIME) }}</b><br><br>

    Чтобы не терять время зря, рекомендуем вам ознакомиться с <b><a href="/rules">Правилами сайта</a></b><br><br>

    При систематическом игноририровании предупреждений администрации ваш профиль может быть удален<br>
    Востановление профиля или данных после этого будет невозможным<br>
    Будьте внимательны, старайтесь не нарушать больше правил<br><br>
@stop
