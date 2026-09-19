<x-mail.layout :subject="$subject" :preheader="__('mailer.recovery_preheader')">
    <x-mail.heading>{{ __('mailer.hello', ['username' => $username]) }}</x-mail.heading>

    <x-mail.text>
        {{ __('mailer.recovery_intro', ['site' => setting('title')]) }}
    </x-mail.text>

    <x-mail.button :url="$resetUrl" color="error">{{ __('mailer.restore_password') }}</x-mail.button>

    <x-mail.text muted>{{ __('mailer.ignore_if_not_you') }}</x-mail.text>

    <x-slot:subcopy>
        {{ __('mailer.follow_link') }}:<br>
        <x-mail.link :url="$resetUrl">{{ $resetUrl }}</x-mail.link>
    </x-slot:subcopy>
</x-mail.layout>
