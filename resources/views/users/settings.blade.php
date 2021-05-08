@extends('layout')

@section('title', __('index.my_settings'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->getName() }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.my_settings') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form method="post" action="/settings">
            @csrf
            <div class="mb-3{{ hasError('themes') }}">
                <label for="themes" class="form-label">{{ __('users.theme') }}:</label>

                <?php $inputThemes = getInput('themes', $user->themes); ?>
                <select class="form-select" name="themes" id="themes">
                    @foreach ($setting['themes'] as $theme)
                        <?php $selected = ($theme === $inputThemes) ? ' selected' : ''; ?>
                        <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('themes') }}</div>
            </div>

            <?php $inputLang = getInput('language', $user->language); ?>
            <div class="mb-3{{ hasError('language') }}">
                <label for="language" class="form-label">{{ __('users.language') }}:</label>

                <select class="form-select" name="language" id="language">
                    @foreach ($setting['languages'] as $language)
                        <?php $selected = ($language === $inputLang) ? ' selected' : ''; ?>
                        <option value="{{ $language }}"{{ $selected }}>{{ $language }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('language') }}</div>
            </div>

            <?php $inputTimezone = (int) getInput('timezone', $user->timezone); ?>
            <div class="mb-3{{ hasError('timezone') }}">
                <label for="timezone" class="form-label">{{ __('users.time_shifting') }} {{ dateFixed(SITETIME, 'H:i') }}:</label>

                <select class="form-select" name="timezone" id="timezone">
                    @foreach ($setting['timezones'] as $timezone)
                        <?php $selected = ($timezone === $inputTimezone) ? ' selected' : ''; ?>
                        <option value="{{ $timezone }}"{{ $selected }}>{{ $timezone }}</option>
                    @endforeach
                </select>

                <div class="invalid-feedback">{{ textError('timezone') }}</div>
            </div>

            <div class="form-check">
                <input type="hidden" value="0" name="notify">
                <input type="checkbox" class="form-check-input" value="1" name="notify" id="notify"{{ getInput('notify', $user->notify) ? ' checked' : '' }}>
                <label data-bs-toggle="tooltip" title="{{ __('users.notification_hint') }}" class="form-check-label" for="notify">{{ __('users.receive_notification') }}</label>
            </div>

            <div class="form-check">
                <input type="hidden" value="0" name="subscribe">
                <input type="checkbox" class="form-check-input" value="1" name="subscribe" id="subscribe"{{ getInput('subscribe', $user->subscribe) ? ' checked' : '' }}>
                <label data-bs-toggle="tooltip" title="{{ __('users.newsletters_hint') }}" class="form-check-label" for="subscribe">{{ __('users.receive_newsletters') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
