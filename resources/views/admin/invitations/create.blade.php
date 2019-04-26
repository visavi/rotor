@extends('layout')

@section('title')
    Создание ключей
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/invitations">Приглашения</a></li>
            <li class="breadcrumb-item active">Создание ключей</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>Генерация новых ключей</h3>
    <div class="form">
        <form action="/admin/invitations/create" method="post">
            @csrf
            <?php $inputKeys = (int) getInput('keys'); ?>
            <div class="form-group{{ hasError('keys') }}">
                <label for="keys">Количество ключей:</label>
                <select class="form-control" name="keys" id="keys">
                    @foreach ($listKeys as $key)
                        <?php $selected = ($key === $inputKeys) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $key }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('keys') }}</div>
            </div>

            <button class="btn btn-primary">Создать</button>
        </form>
    </div><br>

    <h3>Отправить ключ пользователю</h3>
    <div class="form">
        <form action="/admin/invitations/send" method="post">
            @csrf
            <div class="form-group{{ hasError('user') }}">
                <label for="user">Логин пользователя:</label>
                <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" required>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </div>

            <?php $inputKeys = (int) getInput('userkeys'); ?>
            <div class="form-group{{ hasError('userkeys') }}">
                <label for="userkeys">Количество ключей:</label>

                <select class="form-control" name="userkeys" id="userkeys">

                    @foreach ($listKeys as $key)
                        <?php $selected = ($key === $inputKeys) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $key }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('userkeys') }}</div>
            </div>

            <button class="btn btn-primary">Отправить</button>
        </form>
    </div><br>

    @if (isAdmin('boss'))
        <h3>Рассылка ключей</h3>
        <div class="form">
            Разослать ключи активным пользователям:<br>
            <form action="/admin/invitations/mail" method="post">
                @csrf
                <button class="btn btn-primary">Разослать</button>
            </form>
        </div><br>
    @endif
@stop
