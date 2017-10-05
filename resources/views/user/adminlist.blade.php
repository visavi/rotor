@extends('layout')

@section('title')
    Список администраторов
@stop

@section('content')

    <h1>Список администраторов</h1>

    @if ($users->isNotEmpty())

        @foreach($users as $user)
            {!! $user->getGender() !!} <b>{!! profile($user) !!}</b>
            ({{ userLevel($user->level) }}) {!! userOnline($user) !!}<br>
        @endforeach

        <br>Всего в администрации: <b>{{ $users->count() }}</b><br><br>

        @if (getUser())
            <h3>Быстрая почта</h3>

            <div class="form">
                <form method="post" action="/private/send">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                    Выберите адресат:<br>
                    <select name="user">
                        @foreach($users as $user)
                            <option value="{{ $user->login }}">{{ $user->login }}</option>
                        @endforeach
                    </select><br>
                    Сообщение:<br>
                    <textarea cols="25" rows="5" name="msg"></textarea><br>

                    @if (getUser('point') < setting('privatprotect'))
                        Проверочный код:<br>
                        <img src="/captcha" alt=""><br>
                        <input name="protect" size="6" maxlength="6"><br>
                    @endif

                    <button class="btn btn-primary">Отправить</button>
                </form>
            </div><br>
        @endif
    @else
        {{ showError('Администрации еще нет!') }}
    @endif
@stop
