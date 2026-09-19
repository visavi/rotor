{{ __('mailer.hello', ['username' => $username]) }}

{{ __('mailer.change_mail_intro') }}

{{ __('mailer.change_email') }}: {{ $changeUrl }}

{{ __('mailer.change_mail_expires') }}
{{ __('mailer.change_mail_auth_required') }}

{{ __('mailer.ignore_if_not_you') }}

--
{{ setting('copy') }}
{{ config('app.url') }}
