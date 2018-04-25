@if (getUser())
    @if (getUser('newprivat'))
        @if (! App\Classes\Request::is('bans', 'key', 'messages', 'rules', 'closed', 'login', 'register'))
            <a class="btn btn-danger btn-sm" href="/messages"><i class="fa fa-envelope"></i> Приватное сообщение! <span class="badge badge-light">{{ getUser('newprivat') }}</span></a>
        @endif
    @endif

    @if (getUser('newwall'))
        @if (! App\Classes\Request::is('bans', 'key', 'walls', 'rules', 'closed', 'login', 'register'))
            <a class="btn btn-primary btn-sm" href="/walls/{{ getUser('login') }}"><i class="fa fa-sticky-note"></i> Запись на стене! <span class="badge badge-light">{{ getUser('newwall') }}</span></a>
        @endif
    @endif
@endif
