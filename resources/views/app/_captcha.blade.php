@if (setting('captcha_type') === 'recaptcha_v2')
    <script src="//www.google.com/recaptcha/api.js?hl={{ app()->getLocale() }}" async defer></script>
    <div class="g-recaptcha mb-3" data-sitekey="{{ setting('recaptcha_public') }}"></div>
    <div class="invalid-feedback">{{ textError('protect') }}</div>
@endif

@if (setting('captcha_type') === 'recaptcha_v3')
    <script src="//www.google.com/recaptcha/api.js?onload=recaptchaCallback&amp;render={{ setting('recaptcha_public') }}&amp;hl={{ app()->getLocale() }}" async defer></script>
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

@if (in_array(setting('captcha_type'), ['graphical', 'animated'], true))
    <div class="mb-3{{ hasError('protect') }}">
        <label for="protect" class="form-label">{{ __('main.verification_code') }}:</label><br>
        <img src="/captcha" onclick="this.src='/captcha?'+Math.random()" class="rounded cursor-pointer mb-1" alt="Captcha"><br>
        <input class="form-control w-25" name="protect" id="protect" maxlength="8" autocomplete="off" required>
        <div class="invalid-feedback">{{ textError('protect') }}</div>
    </div>
@endif

@push('styles')
    <style>
        .g-recaptcha {
            overflow:hidden;
            width:298px;
            height:74px;
        }

        iframe {
            margin:-1px 0 0 -2px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            const currentTheme = $('html').data('bs-theme');
            $('.g-recaptcha').attr("data-theme", currentTheme);
        });
    </script>
@endpush
