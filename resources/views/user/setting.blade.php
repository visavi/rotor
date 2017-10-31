@extends('layout')

@section('title')
    Мои настройки
@stop

@section('content')

    <h1>Мои настройки</h1>

    <i class="fa fa-book"></i>
    <a href="/user/{{ $user->login }}">Моя анкета</a> /
    <a href="/profile">Мой профиль</a> /
    <a href="/account">Мои данные</a> /
    <b>Настройки</b><hr>

    <div class="form">
        <form method="post" action="setting">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('themes') }}">
                <label for="themes">Тема:</label>

                <select class="form-control" name="themes" id="themes">
                    <option value="0">Автоматически</option>

                    @foreach ($setting['themes'] as $theme)
                        <option value="{{ basename($theme) }}"{{ getUser('themes') == basename($theme) ? 'selected' : '' }}>{{ basename($theme) }}</option>
                    @endforeach
                </select>

                {!! textError('themes') !!}
            </div>

            <div class="form-group{{ hasError('lang') }}">
                <label for="lang">Язык:</label>

                <select class="form-control" name="lang" id="lang">
                    @foreach ($setting['languages'] as $lang)
                        <option value="{{ basename($lang) }}"{{ getUser('lang') == basename($lang) ? 'selected' : '' }}>{{ $setting['langShort'][basename($lang)] }}</option>
                    @endforeach
                </select>

                {!! textError('lang') !!}
            </div>

            <div class="form-group{{ hasError('timezone') }}">
                <label for="timezone">Временной сдвиг {{ dateFixed(SITETIME, 'H:i') }}:</label>

                <select class="form-control" name="timezone" id="timezone">';
                    @foreach($setting['timezones'] as $timezone)
                        <option value="{{ $timezone }}"{{ getUser('timezone') == $timezone ? ' selected' : '' }}>{{ $timezone }}</option>
                    @endforeach
                </select>

                {!! textError('timezone') !!}
            </div>

            <div class="checkbox">
                <label data-toggle="tooltip" title="Уведомления об ответах будут приходить в личные сообщения">
                    <input name="notify" type="checkbox" value="1"{{ getUser('notify') ? ' checked' : '' }}> Получать уведомления об ответах
                </label>
            </div>

            <div class="checkbox">
                <label data-toggle="tooltip" title="Получение информационных писем с сайта на email">
                    <input name="subscribe" type="checkbox" value="1"{{ getUser('subscribe') ? ' checked' : '' }}> Получать информационные письма
                </label>
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
