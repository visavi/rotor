{{ __('mailer.hello', ['username' => $username]) }}

{{ __('mailer.change_password_intro') }}

{{ __('mailer.new_password') }}: {{ $password }}

{{ __('mailer.keep_password_safe') }}

--
{{ setting('copy') }}
{{ config('app.url') }}
