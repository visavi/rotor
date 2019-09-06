@if (setting('captcha_type') === 'recaptcha_v2')
    <script src="//www.google.com/recaptcha/api.js?hl={{ setting('language') }}" async defer></script>
    <div class="g-recaptcha" data-sitekey="{{ setting('recaptcha_public') }}"></div>
    <div class="invalid-feedback">{{ textError('protect') }}</div>
@endif

@if (setting('captcha_type') === 'recaptcha_v3')
    <script src="//www.google.com/recaptcha/api.js?onload=recaptchaCallback&amp;render={{ setting('recaptcha_public') }}&amp;hl={{ setting('language') }}" async defer></script>
    <script>
        function recaptchaCallback() {
            grecaptcha.ready(function () {
                grecaptcha.execute('{{ setting('recaptcha_public') }}').then(function (token) {
                    $('#recaptchaResponse').val(token);
                });
            });
        }
    </script>
    <input type="hidden" name="protect" id="recaptchaResponse">
@endif

@if (setting('captcha_type') === 'graphical')
    <div class="form-group{{ hasError('protect') }}">
        <label for="protect">{{ __('main.verification_code') }}:</label><br>
        <img src="/captcha" onclick="this.src='/captcha?'+Math.random()" class="rounded" alt="" style="cursor: pointer;"><br>
        <input class="form-control" name="protect" id="protect" maxlength="6" required>
        <div class="invalid-feedback">{{ textError('protect') }}</div>
    </div>
@endif
