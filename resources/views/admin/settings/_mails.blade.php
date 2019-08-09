@section('header')
    <h1>{{ trans('settings.mails') }}</h1>
@stop

<form action="/admin/settings?act=mails" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[sendprivatmailday]') }}">
        <label for="sendprivatmailday">Кол. дней перед отправкой уведомления о привате на email:</label>
        <input type="number" class="form-control" id="sendprivatmailday" name="sets[sendprivatmailday]" maxlength="2" value="{{ getInput('sets.sendprivatmailday', $settings['sendprivatmailday']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[sendprivatmailday]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[sendmailpacket]') }}">
        <label for="sendmailpacket">Рассылка писем на email за одну операцию:</label>
        <input type="number" class="form-control" id="sendmailpacket" name="sets[sendmailpacket]" maxlength="3" value="{{ getInput('sets.sendmailpacket', $settings['sendmailpacket']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[sendmailpacket]') }}</div>
    </div>

    <button class="btn btn-primary">{{ trans('main.save') }}</button>
</form>
