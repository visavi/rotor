@extends('layout')

@section('title')
    Список администраторов
@stop

@section('content')

    <h1>Список администраторов</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Список администраторов</li>
        </ol>
    </nav>

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
                        {!! view('app/_captcha') !!}
                    @endif

                    <button class="btn btn-primary">Отправить</button>
                </form>
            </div><br>
        @endif
    @else
        {!! showError('Администрации еще нет!') !!}
    @endif
@stop
