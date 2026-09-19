<x-mail.layout :subject="$subject" :preheader="__('mailer.restore_preheader')">
    <x-mail.heading>{{ __('mailer.hello', ['username' => $username]) }}</x-mail.heading>

    <x-mail.text>
        {{ __('mailer.restore_intro', ['site' => setting('title')]) }}
    </x-mail.text>

    <x-mail.panel>
        <strong>{{ __('mailer.login') }}:</strong> {{ $login }}<br>
        <strong>{{ __('mailer.password') }}:</strong> {{ $password }}
    </x-mail.panel>

    <x-mail.text muted>{{ __('mailer.change_password_in_profile') }}</x-mail.text>
</x-mail.layout>
