<x-mail.layout :subject="$subject" :preheader="__('mailer.change_password_preheader')">
    <x-mail.heading>{{ __('mailer.hello', ['username' => $username]) }}</x-mail.heading>

    <x-mail.text>{{ __('mailer.change_password_intro') }}</x-mail.text>

    <x-mail.panel>
        <strong>{{ __('mailer.new_password') }}:</strong> {{ $password }}
    </x-mail.panel>

    <x-mail.text muted>{{ __('mailer.keep_password_safe') }}</x-mail.text>
</x-mail.layout>
