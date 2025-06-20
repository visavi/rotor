@section('header')
    <h1>{{ __('settings.protects') }}</h1>
@stop

<form method="post">
    @csrf
    <?php $inputCaptcha = getInput('sets.captcha_type', $settings['captcha_type']); ?>

    <div class="mb-3{{ hasError('sets[captcha_type]') }}">
        <label for="captcha_type" class="form-label">{{ __('settings.captcha_type') }}:</label>
        <select class="form-select" id="captcha_type" name="sets[captcha_type]">

            @foreach ($protects as $key => $captcha)
                <?php $selected = ($key === $inputCaptcha) ? ' selected' : ''; ?>
                <option value="{{ $key }}"{{ $selected }}>{{ $captcha }}</option>
            @endforeach

        </select>
        <div class="invalid-feedback">{{ textError('sets[captcha_type]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[captcha_symbols]') }}">
        <label for="captcha_symbols" class="form-label">{{ __('settings.captcha_valid_symbols') }} [a-z0-9]:</label>
        <input pattern="[a-z0-9]+" type="text" class="form-control" id="captcha_symbols" name="sets[captcha_symbols]" maxlength="36" value="{{ getInput('sets.captcha_symbols', $settings['captcha_symbols']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[captcha_symbols]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[captcha_maxlength]') }}">
        <label for="captcha_maxlength" class="form-label">{{ __('settings.captcha_symbols') }} [4-8]:</label>
        <input type="number" min="4" max="8" class="form-control" id="captcha_maxlength" name="sets[captcha_maxlength]" maxlength="1" value="{{ getInput('sets.captcha_maxlength', $settings['captcha_maxlength']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[captcha_maxlength]') }}</div>
    </div>

    <h3 class="mt-3">reCaptcha</h3>

    <div class="mb-3{{ hasError('sets[recaptcha_public]') }}">
        <label for="recaptcha_public" class="form-label">{{ __('settings.captcha_public') }}:</label>
        <input type="hidden" name="opt[recaptcha_public]" value="1">
        <input type="text" class="form-control" id="recaptcha_public" name="sets[recaptcha_public]" value="{{ getInput('sets.recaptcha_public', $settings['recaptcha_public']) }}">
        <div class="invalid-feedback">{{ textError('sets[recaptcha_public]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[recaptcha_private]') }}">
        <label for="recaptcha_private" class="form-label">{{ __('settings.captcha_private') }}:</label>
        <input type="hidden" name="opt[recaptcha_private]" value="1">
        <input type="text" class="form-control" id="recaptcha_private" name="sets[recaptcha_private]" value="{{ getInput('sets.recaptcha_private', $settings['recaptcha_private']) }}">
        <div class="invalid-feedback">{{ textError('sets[recaptcha_private]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
