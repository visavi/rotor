{{ __('mailer.hello', ['username' => $username]) }}

{{ __('mailer.birthday_text') }}

{{ __('mailer.site_administration', ['site' => setting('title')]) }}

{{ __('mailer.unsubscribe_hint') }} {{ __('mailer.unsubscribe') }}: {{ url('/unsubscribe?key=' . $unsubscribe) }}

--
{{ setting('copy') }}
{{ config('app.url') }}
