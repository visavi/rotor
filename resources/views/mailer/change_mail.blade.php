<x-mail.layout :subject="$subject" :preheader="__('mailer.change_mail_preheader')">
    <x-mail.heading>{{ __('mailer.hello', ['username' => $username]) }}</x-mail.heading>

    <x-mail.text>{{ __('mailer.change_mail_intro') }}</x-mail.text>

    <x-mail.button :url="$changeUrl">{{ __('mailer.change_email') }}</x-mail.button>

    <x-mail.text muted>
        {{ __('mailer.change_mail_expires') }} {{ __('mailer.change_mail_auth_required') }}
    </x-mail.text>

    <x-mail.text muted>{{ __('mailer.ignore_if_not_you') }}</x-mail.text>

    <x-slot:subcopy>
        {{ __('mailer.follow_link') }}:<br>
        <x-mail.link :url="$changeUrl">{{ $changeUrl }}</x-mail.link>
    </x-slot:subcopy>
</x-mail.layout>
