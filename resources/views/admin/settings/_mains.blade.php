@section('header')
    <h1>{{ __('settings.mains') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[title]') }}">
        <label for="title" class="form-label">{{ __('settings.page_title') }}:</label>
        <input type="text" class="form-control" id="title" name="sets[title]" maxlength="100" value="{{ old('sets.title', $settings['title']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[title]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[logos]') }}">
        <label for="logos" class="form-label">{{ __('settings.caption_top') }}:</label>
        <input type="text" class="form-control" id="logos" name="sets[logos]" maxlength="100" value="{{ old('sets.logos', $settings['logos']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[logos]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[copy]') }}">
        <label for="copy" class="form-label">{{ __('settings.caption_bottom') }}:</label>
        <input type="text" class="form-control" id="copy" name="sets[copy]" maxlength="100" value="{{ old('sets.copy', $settings['copy']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[copy]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[logotip]') }}">
        <label for="logotip" class="form-label">{{ __('settings.logotype') }}:</label>
        <input type="text" class="form-control" id="logotip" name="sets[logotip]" maxlength="100" value="{{ old('sets.logotip', $settings['logotip']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[logotip]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[floodstime]') }}">
        <label for="floodstime" class="form-label">{{ __('settings.antiflood') }}:</label>
        <input type="number" class="form-control" id="floodstime" name="sets[floodstime]" maxlength="3" value="{{ old('sets.floodstime', $settings['floodstime']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[floodstime]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[doslimit]') }}">
        <label for="doslimit" class="form-label">{{ __('settings.request_limit') }}:</label>
        <input type="number" class="form-control" id="doslimit" name="sets[doslimit]" maxlength="3" value="{{ old('sets.doslimit', $settings['doslimit']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[doslimit]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[currency]') }}">
        <label for="currency" class="form-label">{{ __('settings.currency') }}:</label>
        <input type="text" class="form-control" id="currency" name="sets[currency]" maxlength="10" value="{{ old('sets.currency', $settings['currency']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[currency]') }}</div>
    </div>

    <?php $languages = array_map('basename', glob(resource_path('lang/*'), GLOB_ONLYDIR)); ?>
    <?php $inputLang = old('language', $settings['language']); ?>

    <div class="mb-3{{ hasError('sets[language]') }}">
        <label for="language" class="form-label">{{ __('settings.language') }}:</label>
        <select class="form-select" id="language" name="sets[language]">
            @foreach ($languages as $language)
                <?php $selected = ($language === $inputLang) ? ' selected' : ''; ?>
                <option value="{{ $language }}"{{ $selected }}>{{ $language }}</option>
            @endforeach
        </select>

        <div class="invalid-feedback">{{ textError('sets[language]') }}</div>
    </div>

    <?php $inputLangFallback = old('language_fallback', $settings['language_fallback']); ?>

    <div class="mb-3{{ hasError('sets[language_fallback]') }}">
        <label for="language_fallback" class="form-label">{{ __('settings.fallback_language') }}:</label>
        <select class="form-select" id="language_fallback" name="sets[language_fallback]">
            @foreach ($languages as $language)
                <?php $selected = ($language === $inputLangFallback) ? ' selected' : ''; ?>
                <option value="{{ $language }}"{{ $selected }}>{{ $language }}</option>
            @endforeach
        </select>

        <div class="invalid-feedback">{{ textError('sets[language_fallback]') }}</div>
    </div>

    <p class="text-muted fst-italic">
        {{ __('settings.fallback_language_hint') }}
    </p>

    <?php $themes = array_map('basename', glob(resource_path('views/themes/*'), GLOB_ONLYDIR)); ?>
    <?php $inputThemes = old('sets.themes', $settings['themes']); ?>

    <div class="mb-3{{ hasError('sets[themes]') }}">
        <label for="themes" class="form-label">{{ __('settings.theme') }}:</label>
        <select class="form-select" id="themes" name="sets[themes]">
            @foreach ($themes as $theme)
                <?php $selected = ($inputThemes === $theme) ? ' selected' : ''; ?>
                <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback">{{ textError('sets[themes]') }}</div>
    </div>

    <?php $inputSite = old('sets.closedsite', $settings['closedsite']); ?>

    <div class="mb-3{{ hasError('sets[closedsite]') }}">
        <label for="closedsite" class="form-label">{{ __('settings.access_site') }}:</label>
        <select class="form-select" id="closedsite" name="sets[closedsite]">
            @foreach ($statsite as $key => $stat)
                <?php $selected = ($key === (int) $settings['closedsite']) ? ' selected' : ''; ?>
                <option value="{{ $key }}"{{ $selected }}>{{ $stat }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback">{{ textError('sets[closedsite]') }}</div>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" value="0" name="sets[openreg]">
        <input type="checkbox" class="form-check-input" value="1" name="sets[openreg]" id="openreg"{{ old('sets.openreg', $settings['openreg']) ? ' checked' : '' }}>
        <label class="form-check-label" for="openreg">{{ __('settings.registration_allow') }}</label>
    </div>

    <div class="mb-3">
        <label for="email_mode" class="form-label">{{ __('settings.registration_email_mode') }}:</label>
        <select class="form-select" id="email_mode" name="sets[email_mode]">
            @foreach ($emailModes as $key => $title)
                <?php $selected = ($key === old('sets.email_mode', $settings['email_mode'])) ? ' selected' : ''; ?>
                <option value="{{ $key }}"{{ $selected }}>{{ $title }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback">{{ textError('sets[email_mode]') }}</div>
    </div>

    <p class="text-muted fst-italic">
        {{ __('settings.registration_email_mode_hint') }}
    </p>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
