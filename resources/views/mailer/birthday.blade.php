<x-mail.layout :subject="$subject" :preheader="__('mailer.birthday_preheader')">
    <x-mail.heading>{{ __('mailer.hello', ['username' => $username]) }}</x-mail.heading>

    <x-mail.text>{{ __('mailer.birthday_text') }}</x-mail.text>

    <x-mail.text>{{ __('mailer.site_administration', ['site' => setting('title')]) }}</x-mail.text>

    <x-mail.text muted>
        {{ __('mailer.unsubscribe_hint') }}
        <x-mail.link :url="url('/unsubscribe?key=' . $unsubscribe)">{{ __('mailer.unsubscribe') }}</x-mail.link>
    </x-mail.text>
</x-mail.layout>
