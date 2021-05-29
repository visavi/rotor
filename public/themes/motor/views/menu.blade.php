@if ($user = getUser())
    <i class="fa fa-user-circle fa-lg"></i> <a href="/users/{{ $user->login }}">{{ $user->getName() }}</a> &bull;

    @if (isAdmin())
        <a href="/admin">{{ __('index.panel') }}</a> &bull;
        @if (statsSpam() > 0)
            <a href="/admin/spam"><span style="color:#ff0000">{{ __('index.complains') }}</span></a> &bull;
        @endif

        @if ($user->newchat < statsNewChat())
            <a href="/admin/chats"><span style="color:#ff0000">{{ __('index.chat') }}</span></a> &bull;
        @endif
    @endif
        <a href="/menu">{{ __('index.menu') }}</a> &bull;
        <a href="/logout?_token={{ csrf_token() }}" onclick="return logout(this)">{{ __('index.logout') }}</a>
@else
    <i class="fa fa-lock fa-lg"></i> <a href="/login{{ returnUrl() }}" rel="nofollow">{{ __('index.login') }}</a> &bull;
    <a href="/register" rel="nofollow">{{ __('index.register') }}</a>
@endif
