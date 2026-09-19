{{ __('mailer.hello', ['username' => $username]) }}

{{ __('mailer.recovery_intro', ['site' => setting('title')]) }}

{{ __('mailer.restore_password') }}: {{ $resetUrl }}

{{ __('mailer.ignore_if_not_you') }}

--
{{ setting('copy') }}
{{ config('app.url') }}
