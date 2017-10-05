@extends('layout')

@section('title')
    Новое сообщение
@stop

@section('content')

    <h1>Новое сообщение</h1>

    @if ($user)

        <i class="fa fa-envelope"></i> Сообщение для <b>{!! profile($user) !!}</b>:<br>
        <i class="fa fa-history"></i> <a href="/private/history?user={{ $user->login  }}">История переписки</a><br>

        @if (isIgnore(getUser(), $user))
            <b><span style="color:#ff0000">Внимание, данный пользователь находится в игнор-листе!</span></b><br>
        @endif

        <div class="form">
            <form action="/private/send?user={{ $user->login }}" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">


                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required></textarea>

                @if (getUser('point') < setting('privatprotect'))
                    Проверочный код:<br>
                    <img src="/captcha" alt=""><br>
                    <input name="protect" size="6" maxlength="6"><br>
                @endif

                <button class="btn btn-primary">Отправить</button>
            </form>
        </div><br>

    @else

        <div class="form">
            <form action="/private/send" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">


                <label for="inputLogin">Логин:</label>
                <input class="form-control" name="user" id="inputLogin" maxlength="20" value="{{ getInput('user') }}">

                @if ($contacts->isNotEmpty())
                    <label for="inputContact">Или выберите из списка</label>
                    <select class="form-control" id="inputContact" name="contact">

                        <option value="0">Список контактов</option>

                        @foreach ($contacts as $data)
                            <option value="{{ $data->contactor->login }}">{{ $data->contactor->login }}</option>
                        @endforeach
                    </select><br>
                @endif


                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения"
                          required>{{ getInput('msg') }}</textarea>


                @if (getUser('point') < setting('privatprotect'))
                    Проверочный код:<br>
                    <img src="/captcha" alt=""><br>
                    <input name="protect" size="6" maxlength="6"><br>
                @endif

                <button class="btn btn-primary">Отправить</button>
            </form>
        </div><br>

        Введите логин или выберите пользователя из своего контакт-листа<br>
    @endif


    <i class="fa fa-arrow-circle-up"></i> <a href="/private">К письмам</a><br>
    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br>
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br>

@stop
