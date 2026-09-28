<x-mail.layout :subject="$subject" :preheader="__('mailer.unread_preheader')">
    <x-mail.heading>{{ __('mailer.hello', ['username' => $username]) }}</x-mail.heading>

    <x-mail.text>{{ __('mailer.unread_intro', ['site' => setting('title'), 'count' => $count]) }}</x-mail.text>

    <x-mail.button :url="route('messages.index')">{{ __('mailer.read_messages') }}</x-mail.button>

    <x-mail.text muted>
        {{ __('mailer.unsubscribe_hint') }}
        <x-mail.link :url="url('/unsubscribe?key=' . $unsubscribe)">{{ __('mailer.unsubscribe') }}</x-mail.link>
    </x-mail.text>

    <x-slot:subcopy>
        {{ __('mailer.follow_link') }}:<br>
        <x-mail.link :url="route('messages.index')">{{ route('messages.index') }}</x-mail.link>
    </x-slot:subcopy>
</x-mail.layout>
