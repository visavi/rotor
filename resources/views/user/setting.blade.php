@extends('layout')

@section('title')
    Мои настройки - @parent
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

            Wap-тема по умолчанию:<br>
            <select name="themes">
                <option value="0">Автоматически</option>

                @foreach ($setting['themes'] as $theme)
                    <?php $selected = (getUser('themes') == basename($theme)) ? ' selected="selected"' : ''; ?>
                    echo '<option value="{{ basename($theme) }}"{{ $selected }}>{{ basename($theme) }}</option>
                @endforeach
            </select>
            <br>

            Язык:<br>
            <select name="lang">
                @foreach ($setting['languages'] as $lang) {
                    <?php $selected = (getUser('lang') == basename($lang)) ? ' selected="selected"' : ''; ?>
                    <option value="{{ basename($lang) }}"{{ $selected }}>{{ $setting['langShort'][basename($lang)] }}</option>
                @endforeach
            </select><br>

            Временной сдвиг:<br>
            <select name="timezone">';
                @foreach($setting['timezones'] as $timezone)
                    <?php $selected = (getUser('timezone') == $timezone) ? ' selected="selected"' : ''; ?>
                    <option value="{{ $timezone }}"{{ $selected }}>{{ $timezone }}</option>
                @endforeach
            </select> - {{ dateFixed(SITETIME, 'H:i') }}<br>

            <?php $checked = (getUser('notify') == 1) ? ' checked="checked"' : ''; ?>
            <div class="checkbox">
                <label data-toggle="tooltip" title="Уведомления об ответах будут приходить в личные сообщения">
                    <input name="notify" type="checkbox" value="1"{{ $checked }}> Получать уведомления об ответах
                </label>
            </div>

            <?php $checked = (! empty(getUser('subscribe'))) ? ' checked="checked"' : ''; ?>
            <div class="checkbox">
                <label data-toggle="tooltip" title="Получение информационных писем с сайта на email">
                    <input name="subscribe" type="checkbox" value="1"{{ $checked }}> Получать информационные письма
                </label>
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form></div><br>

    * Значение всех полей (max.50)<br><br>
@stop
