@extends('layout')

@section('title')
    Создание ключей
@stop

@section('content')

    <h1>Создание ключей</h1>

    <h3>Генерация новых ключей</h3>
    <div class="form">
        <form action="/admin/invitations/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <?php $inputKeys = getInput('keys'); ?>
            <div class="form-group{{ hasError('keys') }}">
                <label for="keys">Количество ключей:</label>

                <select class="form-control" name="keys" id="keys">

                    @foreach ($listKeys as $key)
                        <?php $selected = ($key == $inputKeys) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $key }}</option>
                    @endforeach
                </select>

                {!! textError('keys') !!}
            </div>

            <button class="btn btn-primary">Создать</button>
        </form>
    </div><br>

    <h3>Отправить ключ пользователю</h3>
    <div class="form">
        <form action="/admin/invitations/send" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('user') }}">
                <label for="user">Логин пользователя:</label>
                <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" required>
                {!! textError('user') !!}
            </div>

            <?php $inputKeys = getInput('userkeys'); ?>
            <div class="form-group{{ hasError('userkeys') }}">
                <label for="userkeys">Количество ключей:</label>

                <select class="form-control" name="userkeys" id="userkeys">

                    @foreach ($listKeys as $key)
                        <?php $selected = ($key == $inputKeys) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $key }}</option>
                    @endforeach
                </select>

                {!! textError('userkeys') !!}
            </div>

            <button class="btn btn-primary">Отправить</button>
        </form>
    </div><br>

    @if (isAdmin('boss'))
        <h3>Рассылка ключей</h3>
        <div class="form">
            Разослать ключи активным пользователям:<br>
            <form action="/admin/invitations/mail" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                <button class="btn btn-primary">Разослать</button>
            </form>
        </div><br>
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/invitations">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
