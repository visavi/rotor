@extends('layout')

@section('title')
    Контакт-лист
@stop

@section('content')

    <h1>Контакт-лист</h1>

    @if ($contacts->isNotEmpty())

        <form action="/contact/delete?page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($contacts as $contact)
                <div class="b">
                    <div class="float-right">
                        <a href="/private/send?user={{ $contact->contactor->login }}" title="Написать"><i class="fa fa-reply text-muted"></i></a>
                        <a href="/contact/note/{{ $contact->id }}" title="Заметка"><i class="fa fa-sticky-note text-muted"></i></a>
                        <a href="/transfer?uz={{ $contact->contactor->login }}" title="Перевод"><i class="fa fa-money-bill-alt text-muted"></i></a>
                        <input type="checkbox" name="del[]" value="{{ $contact->id }}">
                    </div>

                    <div class="img">{!! userAvatar($contact->contactor) !!}</div>

                    <b>{!! profile($contact->contactor) !!}</b> <small>({{ dateFixed($contact->created_at) }})</small><br>
                    {!! userStatus($contact->contactor) !!} {!! userOnline($contact->contactor) !!}
                </div>
                <div>
                    @if ($contact->text)
                        Заметка: {!! bbCode($contact->text) !!}<br>
                    @endif
                </div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">Удалить выбранное</button>
            </div>
        </form>

        {!! pagination($page) !!}

        Всего в контактах: <b>{{ $page['total'] }}</b><br>
    @else
        {!! showError('Контакт-лист пуст!') !!}
    @endif

    <div class="form my-3">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <div class="form-inline">
                <div class="form-group{{ hasError('user') }}">
                    <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="Логин пользователя" required>
                </div>

                <button class="btn btn-primary">Добавить</button>
            </div>
            {!! textError('user') !!}
        </form>
    </div>

    <i class="fa fa-ban"></i> <a href="/ignore">Игнор-лист</a><br>
    <i class="fa fa-envelope"></i> <a href="/private">Сообщения</a><br>
@stop
