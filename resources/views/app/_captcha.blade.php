@if (setting('recaptcha_public') && setting('recaptcha_private'))
    <script src="//www.google.com/recaptcha/api.js?hl={{ setting('language') }}" async defer></script>
    <div class="g-recaptcha" data-sitekey="{{ setting('recaptcha_public') }}"></div>
    <div class="invalid-feedback">{{ textError('protect') }}</div>
@else
    <div class="form-group{{ hasError('protect') }}">
        <label for="protect">{{ __('main.verification_code') }}:</label><br>
        <img src="/captcha" onclick="this.src='/captcha?'+Math.random()" class="rounded" alt="" style="cursor: pointer;"><br>
        <input class="form-control" name="protect" id="protect" maxlength="6" required>
        <div class="invalid-feedback">{{ textError('protect') }}</div>
    </div>
@endif
