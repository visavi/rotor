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
        <div class="mb-3">
            @foreach($users as $user)
                <div  class="text-truncate bg-light my-1">
                    <div class="img">
                        {!! userAvatar($user) !!}
                        {!! userOnline($user) !!}
                    </div>

                    <b>{!! profile($user) !!}</b>
                    ({{ $user->getLevel() }})
                </div>
            @endforeach
        </div>

        Всего в администрации: <b>{{ $users->count() }}</b><br><br>

        @if (getUser())
            <h3>Быстрая почта</h3>

            <div class="form">
                <form method="post" action="/messages/send">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                    <div class="form-group">
                        <label for="user">Выберите адресат:</label>
                        <select class="form-control" id="user" name="user">
                            @foreach($users as $user)
                                <option value="{{ $user->login }}">{{ $user->login }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="msg">Сообщение:</label>
                        <textarea class="form-control" id="msg" rows="5" name="msg" required></textarea>
                    </div>

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
