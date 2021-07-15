@if (($user = getUser()) && $user->isActive())
    @if ($user->newprivat)
        @if (! request()->is('bans', 'key', 'rules', 'closed', 'login', 'register'))
            <a class="btn btn-danger btn-sm" href="/messages"><i class="fa fa-envelope"></i> {{ __('index.private_message') }} <span class="badge bg-light text-dark">{{ $user->newprivat }}</span></a>
        @endif
    @endif

    @if ($user->newwall)
        @if (!request()->is('bans', 'key', 'walls', 'rules', 'closed', 'login', 'register'))
            <a class="btn btn-primary btn-sm" href="/walls/{{ $user->login }}"><i class="fa fa-sticky-note"></i> {{ __('index.wall_post') }} <span class="badge bg-light text-dark">{{ $user->newwall }}</span></a>
        @endif
    @endif
@endif
