@section('header')
    <h1>{{ trans('settings.mains') }}</h1>
@stop

<form action="/admin/settings" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[title]') }}">
        <label for="title">{{ trans('settings.page_description') }}:</label>
        <input type="text" class="form-control" id="title" name="sets[title]" maxlength="100" value="{{ getInput('sets.title', $settings['title']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[title]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[logos]') }}">
        <label for="logos">{{ trans('settings.caption_top') }}:</label>
        <input type="text" class="form-control" id="logos" name="sets[logos]" maxlength="100" value="{{ getInput('sets.logos', $settings['logos']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[logos]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[copy]') }}">
        <label for="copy">{{ trans('settings.caption_bottom') }}:</label>
        <input type="text" class="form-control" id="copy" name="sets[copy]" maxlength="100" value="{{ getInput('sets.copy', $settings['copy']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[copy]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[logotip]') }}">
        <label for="logotip">{{ trans('settings.logotype') }}:</label>
        <input type="text" class="form-control" id="logotip" name="sets[logotip]" maxlength="100" value="{{ getInput('sets.logotip', $settings['logotip']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[logotip]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[floodstime]') }}">
        <label for="floodstime">{{ trans('settings.antiflood') }}:</label>
        <input type="number" class="form-control" id="floodstime" name="sets[floodstime]" maxlength="3" value="{{ getInput('sets.floodstime', $settings['floodstime']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[floodstime]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[doslimit]') }}">
        <label for="doslimit">{{ trans('settings.request_limit') }}:</label>
        <input type="number" class="form-control" id="doslimit" name="sets[doslimit]" maxlength="3" value="{{ getInput('sets.doslimit', $settings['doslimit']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[doslimit]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[timezone]') }}">
        <label for="timezone">{{ trans('settings.timezone') }}:</label>
        <input type="text" class="form-control" id="timezone" name="sets[timezone]" maxlength="50" value="{{ getInput('sets.timezone', $settings['timezone']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[timezone]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[currency]') }}">
        <label for="currency">{{ trans('settings.currency') }}:</label>
        <input type="text" class="form-control" id="currency" name="sets[currency]" maxlength="10" value="{{ getInput('sets.currency', $settings['currency']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[currency]') }}</div>
    </div>

    <?php $languages = array_map('basename', glob(RESOURCES . '/lang/*', GLOB_ONLYDIR)); ?>
    <?php $inputLang = getInput('language', $settings['language']); ?>

    <div class="form-group{{ hasError('sets[language]') }}">
        <label for="language">{{ trans('settings.language') }}:</label>
        <select class="form-control" id="language" name="sets[language]">

            @foreach ($languages as $language)
                <?php $selected = ($language === $inputLang) ? ' selected' : ''; ?>
                <option value="{{ $language }}"{{ $selected }}>{{ $language }}</option>
            @endforeach
        </select>

        <div class="invalid-feedback">{{ textError('sets[language]') }}</div>
    </div>

    <?php $inputLangFallback = getInput('language_fallback', $settings['language_fallback']); ?>

    <div class="form-group{{ hasError('sets[language_fallback]') }}">
        <label for="language_fallback">{{ trans('settings.fallback_language') }}:</label>
        <select class="form-control" id="language_fallback" name="sets[language_fallback]">

            @foreach ($languages as $language)
                <?php $selected = ($language === $inputLangFallback) ? ' selected' : ''; ?>
                <option value="{{ $language }}"{{ $selected }}>{{ $language }}</option>
            @endforeach
        </select>

        <div class="invalid-feedback">{{ textError('sets[language_fallback]') }}</div>
    </div>

    <p class="text-muted font-italic">
        {{ trans('settings.fallback_language_hint') }}
    </p>

    <?php $themes = array_map('basename', glob(HOME . '/themes/*', GLOB_ONLYDIR)); ?>
    <?php $inputThemes = getInput('sets.themes', $settings['themes']); ?>

    <div class="form-group{{ hasError('sets[themes]') }}">
        <label for="themes">{{ trans('settings.wap_theme') }}:</label>
        <select class="form-control" id="themes" name="sets[themes]">

            @foreach ($themes as $theme)
                <?php $selected = ($inputThemes === $theme) ? ' selected' : ''; ?>
                <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
            @endforeach

        </select>
        <div class="invalid-feedback">{{ textError('sets[themes]') }}</div>
    </div>

    <?php $inputThemes = getInput('sets.webthemes', $settings['webthemes']); ?>

    <div class="form-group{{ hasError('sets[webthemes]') }}">
        <label for="webthemes">{{ trans('settings.web_theme') }}:</label>
        <select class="form-control" id="webthemes" name="sets[webthemes]">
            <option value="0">{{ trans('main.disable') }}</option>

            @foreach ($themes as $theme)
                <?php $selected = ($inputThemes === $theme) ? ' selected' : ''; ?>
                <option value="{{ $theme }}"{{ $selected }}>{{ $theme }}</option>
            @endforeach

        </select>
        <div class="invalid-feedback">{{ textError('sets[webthemes]') }}</div>
    </div>

    <?php $inputSite = getInput('sets.closedsite', $settings['closedsite']); ?>

    <div class="form-group{{ hasError('sets[closedsite]') }}">
        <label for="closedsite">{{ trans('settings.access_site') }}:</label>
        <select class="form-control" id="closedsite" name="sets[closedsite]">

            @foreach ($statsite as $key => $stat)
                <?php $selected = ($key === (int) $settings['closedsite']) ? ' selected' : ''; ?>
                <option value="{{ $key }}"{{ $selected }}>{{ $stat }}</option>
            @endforeach

        </select>
        <div class="invalid-feedback">{{ textError('sets[closedsite]') }}</div>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[openreg]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[openreg]" id="openreg"{{ getInput('sets.openreg', $settings['openreg']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="openreg">{{ trans('settings.registration_allow') }}</label>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[regkeys]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[regkeys]" id="regkeys"{{ getInput('sets.regkeys', $settings['regkeys']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="regkeys">{{ trans('settings.registration_confirm') }}</label>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[invite]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[invite]" id="invite"{{ getInput('sets.invite', $settings['invite']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="invite">{{ trans('settings.registration_invite') }}</label>
    </div>

    <p class="text-muted font-italic">
        {{ trans('settings.registration_invite_hint') }}
    </p>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
