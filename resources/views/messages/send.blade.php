@extends('layout')

@section('title')
    Новое сообщение
@stop

@section('content')

    <h1>Новое сообщение</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">Мое меню</a></li>
            <li class="breadcrumb-item"><a href="/messages">Приватные сообщения</a></li>
            <li class="breadcrumb-item active">Новое сообщение</li>
        </ol>
    </nav>

    @if ($user)

        <i class="fa fa-envelope"></i> Сообщение для <b>{!! profile($user) !!}</b>:<br>
        <i class="fa fa-history"></i> <a href="/messages/history?user={{ $user->login  }}">История переписки</a><br>

        @if (isIgnore(getUser(), $user))
            <b><span style="color:#ff0000">Внимание, данный пользователь находится в игнор-листе!</span></b><br>
        @endif

        <div class="form">
            <form action="/messages/send?user={{ $user->login }}" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">Сообщение:</label>
                    <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="Текст сообщения" required>{{ getInput('msg') }}</textarea>
                    {!! textError('msg') !!}
                </div>

                @if (getUser('point') < setting('privatprotect'))
                    {!! view('app/_captcha') !!}
                @endif

                <button class="btn btn-primary">Отправить</button>
            </form>
        </div><br>

    @else

        <div class="form">
            <form action="/messages/send" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <div class="form-group{{ hasError('user') }}">
                    <label for="inputLogin">Логин:</label>
                    <input type="text" class="form-control" id="inputLogin" name="user" maxlength="20" value="{{ getInput('user') }}">
                    {!! textError('user') !!}
                </div>

                @if ($contacts->isNotEmpty())
                    <label for="inputContact">Или выберите из списка</label>
                    <select class="form-control" id="inputContact" name="contact">

                        <option value="0">Список контактов</option>

                        @foreach ($contacts as $data)
                            <option value="{{ $data->contactor->login }}">{{ $data->contactor->login }}</option>
                        @endforeach
                    </select><br>
                @endif

                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">Сообщение:</label>
                    <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="Текст сообщения" required>{{ getInput('msg') }}</textarea>
                    {!! textError('msg') !!}
                </div>

                @if (getUser('point') < setting('privatprotect'))
                    {!! view('app/_captcha') !!}
                @endif

                <button class="btn btn-primary">Отправить</button>
            </form>
        </div><br>

        Введите логин или выберите пользователя из своего контакт-листа<br>
    @endif

    <i class="fa fa-search"></i> <a href="/searchusers">Поиск контактов</a><br>
    <i class="fa fa-envelope"></i> <a href="/messages/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">Контакт</a> / <a href="/ignores">Игнор</a><br>
@stop
