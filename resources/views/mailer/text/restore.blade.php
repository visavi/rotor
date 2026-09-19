{{ __('mailer.hello', ['username' => $username]) }}

{{ __('mailer.restore_intro', ['site' => setting('title')]) }}

{{ __('mailer.login') }}: {{ $login }}
{{ __('mailer.password') }}: {{ $password }}

{{ __('mailer.change_password_in_profile') }}

--
{{ setting('copy') }}
{{ config('app.url') }}
