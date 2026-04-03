<!-- Newspaper Sidebar (Right) -->
<div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
<aside class="app-sidebar">
    @if ($user = getUser())
        <div class="paper-sidebar-section">
            <h4 class="paper-sidebar-title">{{ __('index.my_profile') }}</h4>
            <div class="paper-sidebar-user">
                {{ $user->getAvatarImage() }}
                <div>
                    <strong>{{ $user->getName() }}</strong><br>
                    <small>{{ $user->getStatus() }}</small>
                </div>
            </div>
            <div class="paper-sidebar-links">
                <a href="{{ route('profile') }}">{{ __('index.my_profile') }}</a>
                @if (isAdmin())<a href="{{ route('admin.index') }}" rel="nofollow">{{ __('index.panel') }}</a>@endif
            </div>
        </div>
        <hr class="paper-divider">
    @else
        <div class="paper-sidebar-section">
            <h4 class="paper-sidebar-title">{{ __('users.enter') }}</h4>
            <a href="{{ route('login') }}{{ returnUrl() }}" class="paper-btn">{{ __('index.login') }}</a>
            <a href="{{ route('register') }}" class="paper-btn paper-btn-outline">{{ __('index.register') }}</a>
        </div>
        <hr class="paper-divider">
    @endif

    <div class="paper-sidebar-section">
        <h4 class="paper-sidebar-title">{{ __('index.activity') }}</h4>
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

    <hr class="paper-divider">

    <div class="paper-sidebar-section paper-sidebar-search-section">
        <h4 class="paper-sidebar-title">{{ __('main.search') }}</h4>
        <form action="{{ route('search') }}" method="get" class="paper-sidebar-search">
            <input name="query" class="paper-sidebar-search__input" type="search" placeholder="{{ __('main.search') }}..." minlength="3" maxlength="64" required>
            <button class="paper-sidebar-search__btn"><i class="fa fa-search"></i></button>
        </form>
    </div>
</aside>
