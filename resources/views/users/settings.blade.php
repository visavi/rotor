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
use App\Support\Hook;

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
    {{-- Привязки приходят из модулей: без них вкладку показывать нечего.
         Хук зовём напрямую — его вывод нужен дважды и проверяется на пустоту --}}
    @php($accountSections = Hook::call('accountSections', $user))
    @php($hasSections = trim(preg_replace('/<!--.*?-->/s', '', $accountSections)) !== '')

    <ul class="nav nav-tabs mb-3" data-tabs role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-settings" type="button">
                <i class="fas fa-sliders"></i> {{ __('users.tab_settings') }}
            </button>
        </li>

        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-appearance" type="button">
                <i class="fas fa-palette"></i> {{ __('users.tab_appearance') }}
            </button>
        </li>

        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-account" type="button">
                <i class="fas fa-key"></i> {{ __('users.tab_account') }}
            </button>
        </li>

        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-api" type="button">
                <i class="fas fa-code"></i> {{ __('users.tab_api') }}
            </button>
        </li>

        @if ($hasSections)
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-links" type="button">
                    <i class="fa-solid fa-link"></i> {{ __('users.tab_links') }}
                </button>
            </li>
        @endif
    </ul>

    <div class="tab-content" data-tabs>
        {{-- Настройки отображения и уведомлений — одной формой, как было --}}
        <div class="tab-pane fade show active" id="tab-settings">
            <form method="post" action="/settings">
        @csrf

        <div class="section-form mb-3 shadow">
            <div class="section-title"><i class="fas fa-desktop"></i> {{ __('users.interface') }}</div>

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

        {{-- Настройки модулей: поля сохраняет Registry::onSettingsValidate/onSettingsSave --}}
        @hook('settingsFields', $user)

                <button class="btn btn-primary">{{ __('main.change') }}</button>
            </form>
        </div>

        <div class="tab-pane fade" id="tab-appearance">
            <div class="section-form mb-3 shadow">
                    <div class="section-title"><i class="fas fa-award"></i> {{ __('users.status_change') }}</div>

                    @if ($user->point >= setting('editstatuspoint'))
                        <form method="post" action="/accounts/editstatus">
                            @csrf
                            <label for="status" class="form-label">{{ __('users.personal_status') }}:</label>
                            <div class="input-group{{ hasError('status') }}">
                                <input type="text" class="form-control" id="status" name="status" maxlength="20" value="{{ old('status', $user->status) }}">
                                <button class="btn btn-primary">{{ __('main.change') }}</button>
                            </div>
                            <div class="invalid-feedback">{{ textError('status') }}</div>

                            @if (setting('editstatusmoney'))
                                <div class="form-text">{{ __('main.cost') }}: {{ plural(setting('editstatusmoney'), setting('moneyname')) }}</div>
                            @endif
                        </form>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fa-solid fa-circle-exclamation fa-lg"></i>
                            {{ __('users.status_change_condition', ['point' => plural(setting('editstatuspoint'), setting('scorename'))]) }}
                        </div>
                    @endif
                </div>

            <div class="section-form mb-3 shadow">
                <div class="section-title"><i class="fas fa-palette"></i> {{ __('users.color_change') }}</div>

                @if ($user->point >= setting('editcolorpoint'))
                    <form method="post" action="/accounts/editcolor">
                        @csrf
                        <?php $color = old('color', $user->color); ?>
                        <div class="col-sm-4 mb-3{{ hasError('color') }}">
                            <label for="color" class="form-label">{{ __('users.personal_color') }}:</label>
                            <div class="input-group">
                                <input type="text" name="color" class="form-control colorpicker" id="color" maxlength="7" value="{{ $color }}">
                                <input type="color" class="form-control form-control-color colorpicker-addon" value="{{ $color ?? '#000000' }}">
                            </div>
                            <div class="invalid-feedback">{{ textError('color') }}</div>

                            @if (setting('editcolormoney'))
                                <div class="form-text">{{ __('main.cost') }}: {{ plural(setting('editcolormoney'), setting('moneyname')) }}</div>
                            @endif
                        </div>

                        <button class="btn btn-primary">{{ __('main.change') }}</button>
                    </form>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="fa-solid fa-circle-exclamation fa-lg"></i>
                        {{ __('users.color_change_condition', ['point' => plural(setting('editcolorpoint'), setting('scorename'))]) }}
                    </div>
                @endif
            </div>
        </div>

        <div class="tab-pane fade" id="tab-account">
            <div class="section-form mb-3 shadow">
                    <div class="section-title"><i class="fas fa-envelope"></i> {{ __('users.change_email') }}</div>

                    <form method="post" action="{{ route('accounts.change-mail') }}">
                        @csrf
                        <div class="mb-3{{ hasError('email') }}">
                            <label for="email" class="form-label">{{ __('users.email') }}:</label>
                            <input class="form-control" id="email" name="email" maxlength="50" value="{{ old('email', $user->email) }}">
                            <div class="invalid-feedback">{{ textError('email') }}</div>
                        </div>

                        <div class="mb-3{{ hasError('password') }}">
                            <label for="password" class="form-label">{{ __('users.current_password') }}:</label>
                            <input class="form-control" type="password" id="password" name="password" maxlength="20">
                            <div class="invalid-feedback">{{ textError('password') }}</div>
                        </div>

                        <button class="btn btn-primary">{{ __('main.change') }}</button>
                    </form>

                    <span class="text-muted fst-italic">{{ __('users.email_confirm_condition') }}</span>
                </div>

            <div class="section-form mb-3 shadow">
                <div class="section-title"><i class="fas fa-key"></i> {{ __('users.change_password') }}</div>

                <form method="post" action="/accounts/editpassword">
                    @csrf
                    <div class="mb-3{{ hasError('new_password') }}">
                        <label for="new_password" class="form-label">{{ __('users.new_password') }}:</label>
                        <input class="form-control" id="new_password" name="new_password" maxlength="20" value="{{ old('new_password') }}">
                        <div class="invalid-feedback">{{ textError('new_password') }}</div>
                    </div>

                    <div class="mb-3{{ hasError('confirm_password') }}">
                        <label for="confirm_password" class="form-label">{{ __('users.confirm_password') }}:</label>
                        <input class="form-control" id="confirm_password" name="confirm_password" maxlength="20" value="{{ old('confirm_password') }}">
                        <div class="invalid-feedback">{{ textError('confirm_password') }}</div>
                    </div>

                    <div class="mb-3{{ hasError('old_password') }}">
                        <label for="old_password" class="form-label">{{ __('users.current_password') }}:</label>
                        <input class="form-control" type="password" id="old_password" name="old_password" maxlength="20">
                        <div class="invalid-feedback">{{ textError('old_password') }}</div>
                    </div>

                    <button class="btn btn-primary">{{ __('main.change') }}</button>
                </form>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-api">
            <div class="section-form mb-3 shadow">
                    <div class="section-title"><i class="fas fa-code"></i> {{ __('users.your_token') }}</div>

                    <form method="post" action="/accounts/apikey">
                        @csrf
                        @if ($user->apikey)
                            <div class="col-sm-4 mb-3">
                                <label for="apikey" class="form-label">{{ __('users.token') }}:</label>
                                <div class="input-group">
                                    <input class="form-control" type="text" id="apikey" name="apikey" value="{{ $user->apikey }}">
                                    <span class="input-group-text" onclick="return copyToClipboard(this)" data-bs-toggle="tooltip" title="{{ __('main.copy') }}"><i class="far fa-clipboard"></i></span>
                                </div>
                            </div>

                            <button class="btn btn-primary" name="action" value="change">{{ __('main.change') }}</button>
                            <button class="btn btn-danger" name="action" value="delete">{{ __('main.delete') }}</button>
                        @else
                            <button class="btn btn-primary" name="action" value="create">{{ __('main.create') }}</button>
                        @endif
                    </form>

                    <span class="text-muted fst-italic">
                        {{ __('users.token_required') }} <a href="/api">{{ __('users.api_interface') }}</a>
                    </span>
                </div>
        </div>

        @if ($hasSections)
            <div class="tab-pane fade" id="tab-links">{!! $accountSections !!}</div>
        @endif
    </div>
@stop
