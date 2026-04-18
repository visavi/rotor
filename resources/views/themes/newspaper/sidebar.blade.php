<!-- Newspaper Sidebar (Right) -->
<div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
<aside class="app-sidebar">
    @if ($user = getUser())
        <div class="paper-sidebar-section">
            <div class="paper-sidebar-user">
                {{ $user->getAvatarImage() }}
                <div>
                    <a href="{{ route('users.user', ['login' => $user->login]) }}"><strong>{{ $user->getName() }}</strong></a><br>
                    <small>{{ $user->getStatus() }}</small>
                </div>
            </div>
            <ul class="paper-sidebar-menu paper-sidebar-links">
                <li><a class="menu-item" href="{{ route('menu') }}" rel="nofollow"><i class="menu-icon fas fa-user-cog"></i><span class="menu-label">{{ __('index.menu') }}</span></a></li>
                @if (isAdmin())<li><a class="menu-item" href="{{ route('admin.index') }}" rel="nofollow"><i class="menu-icon fas fa-wrench"></i><span class="menu-label">{{ __('index.panel') }}</span></a></li>@endif
            </ul>
        </div>
        <hr class="paper-divider">
    @else
        <div class="paper-sidebar-section">
            <ul class="paper-sidebar-menu paper-sidebar-links">
                <li><a class="menu-item" href="{{ route('login') }}{{ returnUrl() }}" rel="nofollow"><i class="menu-icon fas fa-sign-in-alt"></i><span class="menu-label">{{ __('index.login') }}</span></a></li>
                <li><a class="menu-item" href="{{ route('register') }}" rel="nofollow"><i class="menu-icon far fa-user"></i><span class="menu-label">{{ __('index.register') }}</span></a></li>
            </ul>
        </div>
        <hr class="paper-divider">
    @endif

    <div class="paper-sidebar-section">
        <ul class="paper-sidebar-menu">
            @hook('sidebarMenuStart')
            <li><a class="menu-item{{ request()->is('forums*', 'topics*') ? ' active' : '' }}" href="{{ route('forums.index') }}"><i class="menu-icon far fa-comment-alt"></i><span class="menu-label">{{ __('index.forums') }}</span><span class="badge menu-badge">{{ statsForum() }}</span></a></li>
            <li><a class="menu-item{{ request()->is('guestbook*') ? ' active' : '' }}" href="{{ route('guestbook.index') }}"><i class="menu-icon far fa-comment"></i><span class="menu-label">{{ __('index.guestbook') }}</span><span class="badge menu-badge">{{ statsGuestbook() }}</span></a></li>
            <li><a class="menu-item{{ request()->is('news*') ? ' active' : '' }}" href="{{ route('news.index') }}"><i class="menu-icon far fa-newspaper"></i><span class="menu-label">{{ __('index.news') }}</span><span class="badge menu-badge">{{ statsNews() }}</span></a></li>
            <li><a class="menu-item{{ request()->is('blogs*', 'articles*') ? ' active' : '' }}" href="{{ route('blogs.index') }}"><i class="menu-icon far fa-sticky-note"></i><span class="menu-label">{{ __('index.blogs') }}</span></a></li>
            <li><a class="menu-item{{ request()->is('loads*', 'downs*') ? ' active' : '' }}" href="{{ route('loads.index') }}"><i class="menu-icon fas fa-download"></i><span class="menu-label">{{ __('index.loads') }}</span></a></li>
            <li><a class="menu-item{{ request()->is('photos*') ? ' active' : '' }}" href="{{ route('photos.index') }}"><i class="menu-icon far fa-image"></i><span class="menu-label">{{ __('index.photos') }}</span><span class="badge menu-badge">{{ statsPhotos() }}</span></a></li>
            <li><a class="menu-item{{ request()->is('boards*', 'item*') ? ' active' : '' }}" href="{{ route('boards.index') }}"><i class="menu-icon far fa-rectangle-list"></i><span class="menu-label">{{ __('index.boards') }}</span><span class="badge menu-badge">{{ statsBoard() }}</span></a></li>
            <li><a class="menu-item{{ request()->is('votes*') ? ' active' : '' }}" href="{{ route('votes.index') }}"><i class="menu-icon fas fa-square-poll-horizontal"></i><span class="menu-label">{{ __('index.votes') }}</span><span class="badge menu-badge">{{ statVotes() }}</span></a></li>
            @hook('sidebarMenuEnd')
        </ul>
    </div>

    <div class="paper-sidebar-section paper-sidebar-search-section">
        <form action="{{ route('search') }}" method="get" class="paper-sidebar-search">
            <input name="query" class="paper-sidebar-search__input" type="search" placeholder="{{ __('main.search') }}..." minlength="3" maxlength="64" required>
            <button class="paper-sidebar-search__btn"><i class="fa fa-search"></i></button>
        </form>
    </div>

    <div class="app-sidebar__footer">
        @hook('sidebarFooterStart')
        <i class="fas fa-globe-americas"></i>
        <a href="{{ route('language', ['lang' => 'ru']) }}{{ returnUrl() }}">RU</a> /
        <a href="{{ route('language', ['lang' => 'en']) }}{{ returnUrl() }}">EN</a>
        <span class="online-counter">@yield('online')</span>
        @hook('sidebarFooterEnd')
    </div>
</aside>
