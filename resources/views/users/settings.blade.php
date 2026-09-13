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

@php
$inputThemes   = old('themes', $user->themes);
$inputLang     = old('language', $user->language);
$inputTimezone = (int) old('timezone', $user->timezone);

$notifications = [
    'notify_comment' => ['label' => __('users.notify_comment'),      'hint' => __('users.notify_comment_hint')],
    'notify_reply'   => ['label' => __('users.notify_reply'),        'hint' => __('users.notify_reply_hint')],
    'notify_mention' => ['label' => __('users.notify_mention'),      'hint' => __('users.notify_mention_hint')],
    'subscribe'      => ['label' => __('users.receive_newsletters'), 'hint' => __('users.newsletters_hint')],
];
@endphp

@section('content')
    <form method="post" action="/settings">
        @csrf

        <div class="section-form mb-3 shadow">
            <div class="section-title"><i class="fas fa-palette"></i> {{ __('users.appearance') }}</div>

            <div class="row">
                <div class="col-md-4 mb-3{{ hasError('themes') }}">
                    <label for="themes" class="form-label">{{ __('users.theme') }}:</label>

                    <select class="form-select" name="themes" id="themes">
                        @foreach ($setting['themes'] as $theme)
                            <option value="{{ $theme }}"{{ $theme === $inputThemes ? ' selected' : '' }}>{{ $theme }}</option>
                        @endforeach
                    </select>

                    <div class="invalid-feedback">{{ textError('themes') }}</div>
                </div>

                <div class="col-md-4 mb-3{{ hasError('language') }}">
                    <label for="language" class="form-label">{{ __('users.language') }}:</label>

                    <select class="form-select" name="language" id="language">
                        @foreach ($setting['languages'] as $language)
                            <option value="{{ $language }}"{{ $language === $inputLang ? ' selected' : '' }}>{{ $language }}</option>
                        @endforeach
                    </select>

                    <div class="invalid-feedback">{{ textError('language') }}</div>
                </div>

                <div class="col-md-4 mb-3{{ hasError('timezone') }}">
                    <label for="timezone" class="form-label">{{ __('users.time_shifting') }} {{ dateFixed(now(), 'H:i') }}:</label>

                    <select class="form-select" name="timezone" id="timezone">
                        @foreach ($setting['timezones'] as $timezone)
                            <option value="{{ $timezone }}"{{ $timezone === $inputTimezone ? ' selected' : '' }}>{{ $timezone }}</option>
                        @endforeach
                    </select>

                    <div class="invalid-feedback">{{ textError('timezone') }}</div>
                </div>
            </div>
        </div>

        <div class="section-form mb-3 shadow">
            <div class="section-title"><i class="fas fa-bell"></i> {{ __('users.notifications') }}</div>

            @foreach ($notifications as $name => $notification)
                <div class="form-check form-switch mb-2">
                    <input type="hidden" value="0" name="{{ $name }}">
                    <input type="checkbox" class="form-check-input" value="1" name="{{ $name }}" id="{{ $name }}"{{ old($name, $user->$name) ? ' checked' : '' }}>
                    <label class="form-check-label" for="{{ $name }}">
                        {{ $notification['label'] }}
                        <i class="fas fa-circle-question text-muted" data-bs-toggle="tooltip" title="{{ $notification['hint'] }}"></i>
                    </label>
                </div>
            @endforeach
        </div>

        <button class="btn btn-primary">{{ __('main.change') }}</button>
    </form>
@stop
