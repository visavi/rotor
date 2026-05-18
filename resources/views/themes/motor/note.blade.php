@if (($user = getUser()) && $user->isActive())
    @if ($user->newprivat)
        @if (! request()->is('bans', 'key', 'rules', 'closed', 'login', 'register'))
            <a class="btn btn-danger btn-sm" href="{{ route('messages.index') }}"><i class="fa fa-envelope"></i> {{ __('index.private_message') }} <span class="badge bg-adaptive">{{ $user->newprivat }}</span></a>
        @endif
    @endif
@endif
