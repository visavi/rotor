@if (getUser())
    @if (getUser('newprivat'))
        @if (! Illuminate\Http\Request::createFromGlobals()->is('bans', 'key', 'messages', 'rules', 'closed', 'login', 'register'))
            <a class="btn btn-danger btn-sm" href="/messages"><i class="fa fa-envelope"></i> {{ trans('index.private_message') }} <span class="badge badge-light">{{ getUser('newprivat') }}</span></a>
        @endif
    @endif

    @if (getUser('newwall'))
        @if (! Illuminate\Http\Request::createFromGlobals()->is('bans', 'key', 'walls', 'rules', 'closed', 'login', 'register'))
            <a class="btn btn-primary btn-sm" href="/walls/{{ getUser('login') }}"><i class="fa fa-sticky-note"></i> {{ trans('index.wall_post') }} <span class="badge badge-light">{{ getUser('newwall') }}</span></a>
        @endif
    @endif
@endif
