@if ($user = getUser())
    <i class="fa fa-user-circle fa-lg"></i> <a href="/users/{{ $user->login }}">{{ $user->getName() }}</a> &bull;

    @if (isAdmin())
        <a href="/admin">{{ trans('index.panel') }}</a> &bull;
        @if (statsSpam() > 0)
            <a href="/admin/spam"><span style="color:#ff0000">{{ trans('index.complains') }}</span></a> &bull;
        @endif

        @if ($user->newchat < statsNewChat())
            <a href="/admin/chats"><span style="color:#ff0000">{{ trans('index.chat') }}</span></a> &bull;
        @endif
    @endif
        <a href="/menu">{{ trans('index.menu') }}</a> &bull;
        <a href="/logout?token={{ $_SESSION['token'] }}" onclick="return logout(this)">{{ trans('index.logout') }}</a>
@else
    <i class="fa fa-lock fa-lg"></i> <a href="/login{{ returnUrl() }}" rel="nofollow">{{ trans('index.login') }}</a> &bull;
    <a href="/register" rel="nofollow">{{ trans('index.register') }}</a>
@endif
