{{ __('mailer.hello', ['username' => $username]) }}

{{ __('mailer.unread_intro', ['site' => setting('title'), 'count' => $count]) }}

{{ __('mailer.read_messages') }}: {{ route('messages.index') }}

{{ __('mailer.unsubscribe_hint') }} {{ __('mailer.unsubscribe') }}: {{ url('/unsubscribe?key=' . $unsubscribe) }}

--
{{ setting('copy') }}
{{ config('app.url') }}
