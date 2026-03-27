<div class="wh-header">
    <div class="wh-logo"><a href="{{ route('home') }}">:: {{ setting('title') }} ::</a></div>
    <div class="wh-user">
        @if ($user = getUser())
            @if ($user->newprivat)
                <a href="{{ route('messages.index') }}">[{{ __('messages.new_messages') }}: {{ $user->newprivat }}]</a> &nbsp;
            @endif
            <a href="{{ route('users.user', ['login' => $user->login]) }}">{{ $user->getName() }}</a>
            (<a href="{{ route('menu') }}">{{ $user->getStatus() }}</a>)
            @if (isAdmin())
                &nbsp;<a href="{{ route('admin.index') }}" rel="nofollow">[admin]</a>
            @endif
            &nbsp;
            <form action="{{ route('logout') }}" method="post" class="d-inline" onsubmit="return confirmAction(this)" data-confirm="{{ __('users.confirm_logout') }}">
                @csrf
                <button type="submit" class="wh-btn-link">[{{ __('index.logout') }}]</button>
            </form>
        @else
            <a href="{{ route('login') }}{{ returnUrl() }}">[{{ __('index.login') }}]</a>
            &nbsp;<a href="{{ route('register') }}">[{{ __('index.register') }}]</a>
        @endif
    </div>
</div>

<div class="wh-menu">
    @hook('sidebarMenuStart')
    <a href="{{ route('forums.index') }}"{{ request()->is('forums*', 'topics*') ? ' class="active"' : '' }}>&gt; {{ __('index.forums') }} ({{ statsForum() }})</a>
    <a href="{{ route('guestbook.index') }}"{{ request()->is('guestbook*') ? ' class="active"' : '' }}>&gt; {{ __('index.guestbook') }} ({{ statsGuestbook() }})</a>
    <a href="{{ route('news.index') }}"{{ request()->is('news*') ? ' class="active"' : '' }}>&gt; {{ __('index.news') }} ({{ statsNews() }})</a>
    <a href="{{ route('blogs.index') }}"{{ request()->is('blogs*', 'articles*') ? ' class="active"' : '' }}>&gt; {{ __('index.blogs') }}</a>
    <a href="{{ route('loads.index') }}"{{ request()->is('loads*', 'downs*') ? ' class="active"' : '' }}>&gt; {{ __('index.loads') }}</a>
    <a href="{{ route('photos.index') }}"{{ request()->is('photos*') ? ' class="active"' : '' }}>&gt; {{ __('index.photos') }} ({{ statsPhotos() }})</a>
    <a href="{{ route('boards.index') }}"{{ request()->is('boards*', 'item*') ? ' class="active"' : '' }}>&gt; {{ __('index.boards') }}</a>
    <a href="{{ route('votes.index') }}"{{ request()->is('votes*') ? ' class="active"' : '' }}>&gt; {{ __('index.votes') }}</a>
    @hook('sidebarMenuEnd')
</div>

<div class="wh-search">
    <form action="{{ route('search') }}" method="get">
        <input name="query" type="search" placeholder="{{ __('main.search') }}..." minlength="3" maxlength="64" required>
        <button type="submit">&gt;&gt;</button>
    </form>
</div>
