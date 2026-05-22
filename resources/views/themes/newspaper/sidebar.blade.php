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
