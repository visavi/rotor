@if ($user = getUser())
    <ul class="menu-nav">
        <i class="fa fa-user-circle fa-lg"></i> <a href="{{ route('users.user', ['login' => $user->login]) }}">{{ $user->getName() }}</a> &bull;

        @if (isAdmin())
                <li><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            @if (statsSpam() > 0)
                    <li><a href="{{ route('admin.spam.index') }}"><span style="color:#ff0000">{{ __('index.complains') }}</span></a></li>
            @endif

            @if ($user->newchat < statsNewChat())
                    <li><a href="{{ route('admin.chats.index') }}"><span style="color:#ff0000">{{ __('index.chat') }}</span></a></li>
            @endif
        @endif

    </ul>
@else
    <ul class="menu-nav">
        <li><i class="fa fa-lock fa-lg"></i> <a href="{{ route('login') }}{{ returnUrl() }}" rel="nofollow">{{ __('index.login') }}</a></li>
        <li><a href="{{ route('register') }}" rel="nofollow">{{ __('index.register') }}</a></li>
    </ul>
@endif
