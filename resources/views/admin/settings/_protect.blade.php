@section('header')
    <h1>Защита / Безопасность</h1>
@stop

<form action="/admin/settings?act=protect" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="form-group{{ hasError('sets[captcha_symbols]') }}">
        <label for="captcha_symbols">Допустимые символы captcha [a-z0-9]:</label>
        <input pattern="[a-z0-9]+" type="text" class="form-control" id="captcha_symbols" name="sets[captcha_symbols]" maxlength="36" value="{{ getInput('sets.captcha_symbols', $settings['captcha_symbols']) }}" required>
        {!! textError('sets[captcha_symbols]') !!}
    </div>

    <div class="form-group{{ hasError('sets[captcha_maxlength]') }}">
        <label for="captcha_maxlength">Максимальное количество символов [4-6]:</label>
        <input type="number" min="4" max="6" class="form-control" id="captcha_maxlength" name="sets[captcha_maxlength]" maxlength="1" value="{{ getInput('sets.captcha_maxlength', $settings['captcha_maxlength']) }}" required>
        {!! textError('sets[captcha_maxlength]') !!}
    </div>

    <div class="form-group{{ hasError('sets[captcha_angle]') }}">
        <label for="captcha_angle">Поворот букв [0-30]:</label>
        <input type="number" min="0" max="30" class="form-control" id="captcha_angle" name="sets[captcha_angle]" maxlength="2" value="{{ getInput('sets.captcha_angle', $settings['captcha_angle']) }}" required>
        {!! textError('sets[captcha_angle]') !!}
    </div>

    <div class="form-group{{ hasError('sets[captcha_offset]') }}">
        <label for="captcha_offset">Амплитуда колебаний символов [0-10]:</label>
        <input type="number" min="0" max="10" class="form-control" id="captcha_offset" name="sets[captcha_offset]" maxlength="2" value="{{ getInput('sets.captcha_offset', $settings['captcha_offset']) }}" required>
        {!! textError('sets[captcha_offset]') !!}
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[captcha_distortion]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[captcha_distortion]" id="captcha_distortion"{{ getInput('sets.captcha_distortion', $settings['captcha_distortion']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="captcha_distortion">Искажение</label>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[captcha_interpolation]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[captcha_interpolation]" id="captcha_interpolation"{{ getInput('sets.captcha_interpolation', $settings['captcha_interpolation']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="captcha_interpolation">Размытие</label>
    </div>

    <h3 class="mt-3">reCaptcha</h3>

    <div class="form-group{{ hasError('sets[recaptcha_public]') }}">
        <label for="recaptcha_public">Публичный ключ reCaptcha:</label>
        <input type="hidden" name="opt[recaptcha_public]" value="1">
        <input type="text" class="form-control" id="recaptcha_public" name="sets[recaptcha_public]" value="{{ getInput('sets.recaptcha_public', $settings['recaptcha_public']) }}">
        {!! textError('sets[recaptcha_public]') !!}
    </div>

    <div class="form-group{{ hasError('sets[recaptcha_private]') }}">
        <label for="recaptcha_private">Секретный ключ reCaptcha:</label>
        <input type="hidden" name="opt[recaptcha_private]" value="1">
        <input type="text" class="form-control" id="recaptcha_private" name="sets[recaptcha_private]" value="{{ getInput('sets.recaptcha_private', $settings['recaptcha_private']) }}">
        {!! textError('sets[recaptcha_private]') !!}
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
