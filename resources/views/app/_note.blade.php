@if (isUser())
    @if (user('newprivat'))
        @if (! App\Classes\Request::is('ban', 'key', 'private', 'rules', 'closed', 'login', 'register'))
            <a class="btn btn-danger btn-sm" href="/private"><i class="fa fa-envelope"></i> Приватное сообщение! <span class="badge badge-light">{{ user('newprivat') }}</span></a>
        @endif
    @endif

    @if (user('newwall'))
        @if (! App\Classes\Request::is('ban', 'key', 'wall', 'rules', 'closed', 'login', 'register'))
            <a class="btn btn-primary btn-sm" href="/wall"><i class="fa fa-users"></i> Запись на стене! <span class="badge badge-light">{{ user('newwall') }}</span></a>
        @endif
    @endif
@endif
