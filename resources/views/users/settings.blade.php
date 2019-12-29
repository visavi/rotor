@extends('layout')

@section('title')
    {{ __('index.my_settings') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.my_settings') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form method="post" action="/settings">
            @csrf
            <div class="form-group{{ hasError('themes') }}">
                <label for="themes">{{ __('users.theme') }}:</label>

                <?php $inputThemes = getInput('themes', $user->themes); ?>
                <select class="form-control" name="themes" id="themes">
                    <option value="0">{{ __('main.automatically') }}</option>

                    @foreach ($setting['themes'] as $theme)
                        <?php $selected = ($theme === $inputThemes) ? ' selected' : ''; ?>
                        <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('themes') }}</div>
            </div>

            <?php $inputLang = getInput('language', $user->language); ?>
            <div class="form-group{{ hasError('language') }}">
                <label for="language">{{ __('users.language') }}:</label>

                <select class="form-control" name="language" id="language">
                    @foreach ($setting['languages'] as $language)
                        <?php $selected = ($language === $inputLang) ? ' selected' : ''; ?>
                        <option value="{{ $language }}"{{ $selected }}>{{ $language }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('language') }}</div>
            </div>

            <?php $inputTimezone = (int) getInput('timezone', $user->timezone); ?>
            <div class="form-group{{ hasError('timezone') }}">
                <label for="timezone">{{ __('users.time_shifting') }} {{ dateFixed(SITETIME, 'H:i') }}:</label>

                <select class="form-control" name="timezone" id="timezone">
                    @foreach($setting['timezones'] as $timezone)
                        <?php $selected = ($timezone === $inputTimezone) ? ' selected' : ''; ?>
                        <option value="{{ $timezone }}"{{ $selected }}>{{ $timezone }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('timezone') }}</div>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="notify">
                <input type="checkbox" class="custom-control-input" value="1" name="notify" id="notify"{{ getInput('notify', $user->notify) ? ' checked' : '' }}>
                <label data-toggle="tooltip" title="{{ __('users.notification_hint') }}" class="custom-control-label" for="notify">{{ __('users.receive_notification') }}</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="subscribe">
                <input type="checkbox" class="custom-control-input" value="1" name="subscribe" id="subscribe"{{ getInput('subscribe', $user->subscribe) ? ' checked' : '' }}>
                <label data-toggle="tooltip" title="{{ __('users.newsletters_hint') }}" class="custom-control-label" for="subscribe">{{ __('users.receive_newsletters') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
