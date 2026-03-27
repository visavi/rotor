<!-- Sidebar menu-->
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
                <i class="treeview-indicator fa fa-angle-down"></i>
            </div>

            <ul class="treeview-menu">
                @hook('sidebarTreeviewStart')
                @if (isAdmin())
                    <li>
                        <a class="treeview-item" href="{{ route('admin.index') }}" rel="nofollow">
                            <i class="icon fas fa-wrench"></i>
                            {{ __('index.panel') }}
                        </a>
                    </li>
                @endif
                <li>
                    <a class="treeview-item" href="{{ route('menu') }}" rel="nofollow">
                        <i class="icon fas fa-user-cog"></i>
                        {{ __('index.menu') }}
                    </a>
                </li>
                @hook('sidebarTreeviewEnd')
            </ul>
        @else
            <div class="menu-item" data-bs-toggle="treeview">
                <div class="app-sidebar__user-avatar">
                    <img class="avatar-default rounded-circle" src="/assets/img/images/avatar_guest.png" alt="">
                </div>
                <div class="menu-label">
                    <p class="app-sidebar__user-name">{{ __('users.enter') }}</p>
                </div>
                <i class="treeview-indicator fa fa-angle-down"></i>
            </div>

            <ul class="treeview-menu">
                @hook('sidebarTreeviewGuestStart')
                <li>
                    <a class="treeview-item" href="{{ route('login') }}{{ returnUrl() }}" rel="nofollow">
                        <i class="icon fas fa-sign-in-alt"></i>
                        {{ __('index.login') }}
                    </a>
                </li>
                <li>
                    <a class="treeview-item" href="{{ route('register') }}" rel="nofollow">
                        <i class="icon far fa-user"></i>
                        {{ __('index.register') }}
                    </a>
                </li>
                @hook('sidebarTreeviewGuestEnd')
            </ul>
        @endif
        </li>
    </ul>
    <ul class="app-menu">
        @hook('sidebarMenuStart')
        <li>
            <a class="menu-item{{ request()->is('forums*', 'topics*') ? ' active' : '' }}" href="{{ route('forums.index') }}">
                <i class="menu-icon far fa-comment-alt"></i>
                <span class="menu-label">{{ __('index.forums') }}</span>
                <span class="badge menu-badge">{{ statsForum() }}</span>
            </a>
        </li>

        <li>
            <a class="menu-item{{ request()->is('guestbook*') ? ' active' : '' }}" href="{{ route('guestbook.index') }}">
                <i class="menu-icon far fa-comment"></i>
                <span class="menu-label">{{ __('index.guestbook') }}</span>
                <span class="badge menu-badge">{{ statsGuestbook() }}</span>
            </a>
        </li>

        <li>
            <a class="menu-item{{ request()->is('news*') ? ' active' : '' }}" href="{{ route('news.index') }}">
                <i class="menu-icon far fa-newspaper"></i>
                <span class="menu-label">{{ __('index.news') }}</span>
                <span class="badge menu-badge">{{ statsNews() }}</span>
            </a>
        </li>

        <li class="treeview{{ request()->is('blogs*', 'articles*') ? ' is-expanded' : '' }}">
            <a class="menu-item" href="#" data-bs-toggle="treeview">
                <i class="menu-icon far fa-sticky-note"></i>
                <span class="menu-label">{{ __('index.blogs') }}</span>
                <i class="treeview-indicator fa fa-angle-down"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item{{ request()->routeIs('blogs.index') ? ' active' : '' }}" href="{{ route('blogs.index') }}"><i class="icon fas fa-circle fa-xs"></i> {{ __('blogs.blogs_list') }}</a></li>
                <li><a class="treeview-item{{ request()->routeIs('blogs.main') ? ' active' : '' }}" href="{{ route('blogs.main') }}"><i class="icon fas fa-circle fa-xs"></i> {{ __('blogs.articles_all') }}</a></li>
                <li><a class="treeview-item{{ request()->routeIs('articles.index') ? ' active' : '' }}" href="{{ route('articles.index') }}"><i class="icon fas fa-circle fa-xs"></i> {{ __('blogs.new_articles') }}</a></li>
                <li><a class="treeview-item{{ request()->routeIs('articles.new-comments') ? ' active' : '' }}" href="{{ route('articles.new-comments') }}"><i class="icon fas fa-circle fa-xs"></i> {{ __('blogs.new_comments') }}</a></li>
            </ul>
        </li>

        <li class="treeview{{ request()->is('loads*', 'downs*') ? ' is-expanded' : '' }}">
            <a class="menu-item" href="#" data-bs-toggle="treeview">
                <i class="menu-icon fas fa-download"></i>
                <span class="menu-label">{{ __('index.loads') }}</span>
                <i class="treeview-indicator fa fa-angle-down"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item{{ request()->routeIs('loads.index') ? ' active' : '' }}" href="{{ route('loads.index') }}"><i class="icon fas fa-circle fa-xs"></i> {{ __('loads.loads_list') }}</a></li>
                <li><a class="treeview-item{{ request()->routeIs('downs.new-files') ? ' active' : '' }}" href="{{ route('downs.new-files') }}"><i class="icon fas fa-circle fa-xs"></i> {{ __('loads.new_downs') }}</a></li>
                <li><a class="treeview-item{{ request()->routeIs('downs.new-comments') ? ' active' : '' }}" href="{{ route('downs.new-comments') }}"><i class="icon fas fa-circle fa-xs"></i> {{ __('loads.new_comments') }}</a></li>
            </ul>
        </li>

        <li>
            <a class="menu-item{{ request()->is('photos*') ? ' active' : '' }}" href="{{ route('photos.index') }}">
                <i class="menu-icon far fa-image"></i>
                <span class="menu-label">{{ __('index.photos') }}</span>
                <span class="badge menu-badge">{{ statsPhotos() }}</span>
            </a>
        </li>

        <li>
            <a class="menu-item{{ request()->is('boards*', 'item*') ? ' active' : '' }}" href="{{ route('boards.index') }}">
                <i class="menu-icon far fa-rectangle-list"></i>
                <span class="menu-label">{{ __('index.boards') }}</span>
                <span class="badge menu-badge">{{ statsBoard() }}</span>
            </a>
        </li>

        <li>
            <a class="menu-item{{ request()->is('votes*') ? ' active' : '' }}" href="{{ route('votes.index') }}">
                <i class="menu-icon fas fa-square-poll-horizontal"></i>
                <span class="menu-label">{{ __('index.votes') }}</span>
                <span class="badge menu-badge">{{ statVotes() }}</span>
            </a>
        </li>
        @hook('sidebarMenuEnd')
    </ul>

    <ul class="app-menu app-sidebar__footer">
        @hook('sidebarFooterStart')
        <li class="app-search search-sidebar">
            <form action="{{ route('search') }}" method="get">
                <input name="query" class="form-control app-search__input" type="search" placeholder="{{ __('main.search') }}" minlength="3"  maxlength="64" required>
                <button class="app-search__button"><i class="fa fa-search"></i></button>
            </form>
        </li>

        <li>
            <span class="float-end">
                @yield('online')
            </span>

            <i class="fas fa-globe-americas"></i>
            <a href="{{ route('language', ['lang' => 'ru']) }}{{ returnUrl() }}">RU</a> /
            <a href="{{ route('language', ['lang' => 'en']) }}{{ returnUrl() }}">EN</a>
        </li>
        @hook('sidebarFooterEnd')
    </ul>
</aside>
