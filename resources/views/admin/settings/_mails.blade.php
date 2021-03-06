@section('header')
    <h1>{{ __('settings.mails') }}</h1>
@stop

<form method="post">
    @csrf
    <div class="mb-3{{ hasError('sets[sendprivatmailday]') }}">
        <label for="sendprivatmailday" class="form-label">{{ __('settings.mails_count_days') }}:</label>
        <input type="number" class="form-control" id="sendprivatmailday" name="sets[sendprivatmailday]" maxlength="2" value="{{ getInput('sets.sendprivatmailday', $settings['sendprivatmailday']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[sendprivatmailday]') }}</div>
    </div>

    <div class="mb-3{{ hasError('sets[sendmailpacket]') }}">
        <label for="sendmailpacket" class="form-label">{{ __('settings.mails_send_emails') }}:</label>
        <input type="number" class="form-control" id="sendmailpacket" name="sets[sendmailpacket]" maxlength="3" value="{{ getInput('sets.sendmailpacket', $settings['sendmailpacket']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[sendmailpacket]') }}</div>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
