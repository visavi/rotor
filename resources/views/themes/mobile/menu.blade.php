@if ($user = getUser())
    <i class="fa fa-user-circle fa-lg"></i> <a href="{{ route('users.user', ['login' => $user->login]) }}">{{ $user->getName() }}</a> &bull;

    @if (isAdmin())
        <a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a> &bull;
        @if (statsSpam() > 0)
            <a href="{{ route('admin.spam.index') }}"><span style="color:#ff0000">{{ __('index.complains') }}</span></a> &bull;
        @endif

        @if ($user->newchat < statsNewChat())
            <a href="{{ route('admin.chats.index') }}"><span style="color:#ff0000">{{ __('index.chat') }}</span></a> &bull;
        @endif
    @endif

    <a href="{{ route('menu') }}">{{ __('index.menu') }}</a>
@else
    <i class="fa fa-lock fa-lg"></i> <a href="{{ route('login') }}{{ returnUrl() }}" rel="nofollow">{{ __('index.login') }}</a> &bull;
    <a href="{{ route('register') }}" rel="nofollow">{{ __('index.register') }}</a>
@endif
