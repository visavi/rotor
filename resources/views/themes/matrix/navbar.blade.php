<header id="header">
    <div id="header-top">
        <span id="logo"><a href="{{ route('home') }}">[ {{ setting('title') }} ]</a></span>
        <span id="header-user">
            @if ($user = getUser())
                @if ($user->newprivat)
                    <a href="{{ route('messages.index') }}">&gt;&gt; {{ __('messages.new_messages') }}: {{ $user->newprivat }}</a> |
                @endif
                <a href="{{ route('users.user', ['login' => $user->login]) }}">{{ $user->getName() }}</a>
                [<a href="{{ route('menu') }}">{{ $user->getStatus() }}</a>]
                @if (isAdmin())
                    | <a href="{{ route('admin.index') }}" rel="nofollow">adm</a>
                @endif
                |
                <form action="{{ route('logout') }}" method="post" class="d-inline" onsubmit="return confirmAction(this)" data-confirm="{{ __('users.confirm_logout') }}">
                    @csrf
                    <button type="submit" class="wap-btn-link">{{ __('index.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}{{ returnUrl() }}">{{ __('index.login') }}</a> | <a href="{{ route('register') }}">{{ __('index.register') }}</a>
            @endif
        </span>
    </div>

    <nav id="menu">
        @hook('sidebarMenuStart')
        <a href="{{ route('forums.index') }}"{{ request()->is('forums*', 'topics*') ? ' class="active"' : '' }}>{{ __('index.forums') }}({{ statsForum() }})</a>
        <span class="sep">|</span>
        <a href="{{ route('guestbook.index') }}"{{ request()->is('guestbook*') ? ' class="active"' : '' }}>{{ __('index.guestbook') }}({{ statsGuestbook() }})</a>
        <span class="sep">|</span>
        <a href="{{ route('news.index') }}"{{ request()->is('news*') ? ' class="active"' : '' }}>{{ __('index.news') }}({{ statsNews() }})</a>
        <span class="sep">|</span>
        <a href="{{ route('blogs.index') }}"{{ request()->is('blogs*', 'articles*') ? ' class="active"' : '' }}>{{ __('index.blogs') }}</a>
        <span class="sep">|</span>
        <a href="{{ route('loads.index') }}"{{ request()->is('loads*', 'downs*') ? ' class="active"' : '' }}>{{ __('index.loads') }}</a>
        <span class="sep">|</span>
        <a href="{{ route('photos.index') }}"{{ request()->is('photos*') ? ' class="active"' : '' }}>{{ __('index.photos') }}({{ statsPhotos() }})</a>
        <span class="sep">|</span>
        <a href="{{ route('boards.index') }}"{{ request()->is('boards*', 'item*') ? ' class="active"' : '' }}>{{ __('index.boards') }}</a>
        <span class="sep">|</span>
        <a href="{{ route('votes.index') }}"{{ request()->is('votes*') ? ' class="active"' : '' }}>{{ __('index.votes') }}</a>
        @hook('sidebarMenuEnd')
    </nav>

    <div id="header-search">
        <form action="{{ route('search') }}" method="get">
            &gt; <input name="query" type="search" placeholder="{{ __('main.search') }}..." minlength="3" maxlength="64" required>
            <button type="submit">[go]</button>
        </form>
    </div>
</header>
<div class="wap-hr"></div>
