@extends('layout')

@section('title')
    Мои настройки
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">Мои настройки</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form method="post" action="/settings">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('themes') }}">
                <label for="themes">Тема:</label>

                <?php $inputThemes = getInput('themes', $user->themes); ?>
                <select class="form-control" name="themes" id="themes">
                    <option value="0">Автоматически</option>

                    @foreach ($setting['themes'] as $theme)
                        <?php $selected = ($theme === $inputThemes) ? ' selected' : ''; ?>
                        <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
                    @endforeach
                </select>

                {!! textError('themes') !!}
            </div>

            <?php $inputLang = getInput('language', $user->language); ?>
            <div class="form-group{{ hasError('language') }}">
                <label for="language">Язык:</label>

                <select class="form-control" name="language" id="language">
                    @foreach ($setting['languages'] as $language)
                        <?php $selected = ($language === $inputLang) ? ' selected' : ''; ?>
                        <option value="{{ $language }}"{{ $selected }}>{{ $language }}</option>
                    @endforeach
                </select>

                {!! textError('language') !!}
            </div>

            <?php $inputTimezone = (int) getInput('timezone', $user->timezone); ?>
            <div class="form-group{{ hasError('timezone') }}">
                <label for="timezone">Временной сдвиг {{ dateFixed(SITETIME, 'H:i') }}:</label>

                <select class="form-control" name="timezone" id="timezone">';
                    @foreach($setting['timezones'] as $timezone)
                        <?php $selected = ($timezone === $inputTimezone) ? ' selected' : ''; ?>
                        <option value="{{ $timezone }}"{{ $selected }}>{{ $timezone }}</option>
                    @endforeach
                </select>

                {!! textError('timezone') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="notify">
                <input type="checkbox" class="custom-control-input" value="1" name="notify" id="notify"{{ getInput('notify', $user->notify) ? ' checked' : '' }}>
                <label data-toggle="tooltip" title="Уведомления об ответах будут приходить в личные сообщения" class="custom-control-label" for="notify">Получать уведомления об ответах</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="subscribe">
                <input type="checkbox" class="custom-control-input" value="1" name="subscribe" id="subscribe"{{ getInput('subscribe', $user->subscribe) ? ' checked' : '' }}>
                <label data-toggle="tooltip" title="Получение информационных писем с сайта на email" class="custom-control-label" for="subscribe">Получать информационные письма</label>
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
