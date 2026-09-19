<x-mail.layout :subject="$subject" :preheader="__('mailer.register_preheader')">
    <x-mail.heading>{{ __('mailer.welcome', ['login' => $login]) }}</x-mail.heading>

    <x-mail.text>
        {{ __('mailer.registration_intro', ['site' => setting('title')]) }}
    </x-mail.text>

    <x-mail.panel>
        <strong>{{ __('mailer.login') }}:</strong> {{ $login }}<br>
        <strong>{{ __('mailer.password') }}:</strong> {{ $password }}
    </x-mail.panel>

    @if ($confirmUrl)
        <x-mail.text><strong>{{ __('mailer.activation_text1') }}</strong></x-mail.text>

        <x-mail.button :url="$confirmUrl" color="success">{{ __('mailer.activate_account') }}</x-mail.button>

        <x-mail.text muted>{{ __('mailer.activation_text2') }}</x-mail.text>

        <x-slot:subcopy>
            {{ __('mailer.follow_link') }}:<br>
            <x-mail.link :url="$confirmUrl">{{ $confirmUrl }}</x-mail.link>
        </x-slot:subcopy>
    @else
        <x-mail.button :url="config('app.url')" color="success">{{ __('mailer.enter_site') }}</x-mail.button>

        <x-mail.text muted>{{ __('mailer.registration_text1') }} {{ __('mailer.registration_text2') }}</x-mail.text>
    @endif
</x-mail.layout>
