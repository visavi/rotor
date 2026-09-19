{{ __('mailer.welcome', ['login' => $login]) }}

{{ __('mailer.registration_intro', ['site' => setting('title')]) }}

{{ __('mailer.login') }}: {{ $login }}
{{ __('mailer.password') }}: {{ $password }}

@if ($confirmUrl)
{{ __('mailer.activation_text1') }}

{{ __('mailer.activate_account') }}: {{ $confirmUrl }}

{{ __('mailer.activation_text2') }}
@else
{{ __('mailer.enter_site') }}: {{ config('app.url') }}

{{ __('mailer.registration_text1') }}
{{ __('mailer.registration_text2') }}
@endif

--
{{ setting('copy') }}
{{ config('app.url') }}
