<!-- Nordic Sidebar (Right) -->
<div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
<aside class="app-sidebar">
    <ul class="app-menu user-menu">
        <li class="treeview">
        @if ($user = getUser())
            <div class="menu-item" data-bs-toggle="treeview">
                <div class="app-sidebar__user-avatar">
                    {{ $user->getAvatarImage() }}
                </div>
                <div class="menu-label">
                    <p class="app-sidebar__user-name">{{ $user->getName() }}</p>
                    <p class="app-sidebar__user-designation">{{ $user->getStatus() }}</p>
                </div>
                @if (isAdmin())
                    <i class="treeview-indicator fa fa-angle-down"></i>
                @endif
            </div>
            <ul class="treeview-menu">
                @hook('sidebarTreeviewStart')
                @if (isAdmin())
                    <li><a class="treeview-item" href="{{ route('admin.index') }}" rel="nofollow"><i class="icon fas fa-wrench"></i> {{ __('index.panel') }}</a></li>
                @endif
                @hook('sidebarTreeviewEnd')
            </ul>
        @else
            <div class="menu-item" data-bs-toggle="treeview">
                <div class="app-sidebar__user-avatar">
                    <span class="avatar-default avatar-guest rounded-circle"><i class="fas fa-user"></i></span>
                </div>
                <div class="menu-label">
                    <p class="app-sidebar__user-name">{{ __('users.enter') }}</p>
                </div>
                <i class="treeview-indicator fa fa-angle-down"></i>
            </div>
            <ul class="treeview-menu">
                @hook('sidebarTreeviewGuestStart')
                <li><a class="treeview-item" href="{{ route('login') }}{{ returnUrl() }}" rel="nofollow"><i class="icon fas fa-sign-in-alt"></i> {{ __('index.login') }}</a></li>
                <li><a class="treeview-item" href="{{ route('register') }}" rel="nofollow"><i class="icon far fa-user"></i> {{ __('index.register') }}</a></li>
                @hook('sidebarTreeviewGuestEnd')
            </ul>
        @endif
        </li>
    </ul>

    <ul class="app-menu">
        @hook('sidebarMenu')
    </ul>

    <ul class="app-menu app-sidebar__footer">
        @hook('sidebarFooterStart')
        <li class="app-search search-sidebar">
            <form action="{{ route('search') }}" method="get">
                <input name="query" class="form-control app-search__input" type="search" placeholder="{{ __('main.search') }}" minlength="3" maxlength="64" required>
                <button class="app-search__button"><i class="fa fa-search"></i></button>
            </form>
        </li>
        <li>
            <span class="float-end">{{ showOnline() }}</span>
            <i class="fas fa-globe-americas"></i>
            <a href="#" data-lang="ru">RU</a> /
            <a href="#" data-lang="en">EN</a>
        </li>
        @hook('sidebarFooterEnd')
    </ul>
</aside>
