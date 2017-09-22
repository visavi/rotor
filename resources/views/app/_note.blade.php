@if (getUser())
    @if (getUser('newprivat'))
        @if (! App\Classes\Request::is('ban', 'key', 'private', 'rules', 'closed', 'login', 'register'))
            <a class="btn btn-danger btn-sm" href="/private"><i class="fa fa-envelope"></i> Приватное сообщение! <span class="badge badge-light">{{ getUser('newprivat') }}</span></a>
        @endif
    @endif

    @if (getUser('newwall'))
        @if (! App\Classes\Request::is('ban', 'key', 'wall', 'rules', 'closed', 'login', 'register'))
            <a class="btn btn-primary btn-sm" href="/wall"><i class="fa fa-users"></i> Запись на стене! <span class="badge badge-light">{{ getUser('newwall') }}</span></a>
        @endif
    @endif
@endif
