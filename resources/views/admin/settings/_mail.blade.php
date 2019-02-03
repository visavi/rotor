@section('header')
    <h1>Почта / Рассылка</h1>
@stop

<form action="/admin/settings?act=mail" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="form-group{{ hasError('sets[sendprivatmailday]') }}">
        <label for="sendprivatmailday">Кол. дней перед отправкой уведомления о привате на email:</label>
        <input type="number" class="form-control" id="sendprivatmailday" name="sets[sendprivatmailday]" maxlength="2" value="{{ getInput('sets.sendprivatmailday', $settings['sendprivatmailday']) }}" required>
        {!! textError('sets[sendprivatmailday]') !!}
    </div>

    <div class="form-group{{ hasError('sets[sendmailpacket]') }}">
        <label for="sendmailpacket">Рассылка писем на email за одну операцию:</label>
        <input type="number" class="form-control" id="sendmailpacket" name="sets[sendmailpacket]" maxlength="3" value="{{ getInput('sets.sendmailpacket', $settings['sendmailpacket']) }}" required>
        {!! textError('sets[sendmailpacket]') !!}
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
