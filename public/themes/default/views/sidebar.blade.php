<!-- Sidebar menu-->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar">
    <ul class="app-menu user-menu">
        <li class="treeview">
        @if ($user = getUser())
            <div class="app-menu__item" data-toggle="treeview">
                <div class="app-sidebar__user-avatar">
                    {!! $user->getAvatarImage() !!}
                </div>
                <div class="app-menu__label">
                    <p class="app-sidebar__user-name">{{ $user->getName() }}</p>
                    <p class="app-sidebar__user-designation">{!! $user->getStatus() !!}</p>
                </div>
                <i class="treeview-indicator fa fa-angle-down"></i>
            </div>

            <ul class="treeview-menu">
                @if (isAdmin())
                    <li>
                        <a class="treeview-item" href="/admin" rel="nofollow">
                            <i class="icon fas fa-wrench"></i>
                            {{ __('index.panel') }}
                        </a>
                    </li>
                @endif
                <li>
                    <a class="treeview-item" href="/menu" rel="nofollow">
                        <i class="icon fas fa-user-cog"></i>
                        {{ __('index.menu') }}
                    </a>
                </li>
            </ul>
        @else
            <div class="app-menu__item" data-toggle="treeview">
                <div class="app-sidebar__user-avatar">
                    <img class="img-fluid rounded-circle avatar-default" src="/assets/img/images/avatar_guest.png" alt="">
                </div>
                <div class="app-menu__label">
                    <p class="app-sidebar__user-name">{{ __('users.enter') }}</p>
                </div>
                <i class="treeview-indicator fa fa-angle-down"></i>
            </div>

            <ul class="treeview-menu">
                <li>
                    <a class="treeview-item" href="/login{{ returnUrl() }}" rel="nofollow">
                        <i class="icon fas fa-sign-in-alt"></i>
                        {{ __('index.login') }}
                    </a>
                </li>
                <li>
                    <a class="treeview-item" href="/register" rel="nofollow">
                        <i class="icon far fa-user"></i>
                        {{ __('index.register') }}
                    </a>
                </li>
            </ul>
        @endif
        </li>
    </ul>
    <ul class="app-menu">
        <li>
            <a class="app-menu__item{{ request()->is('forums*', 'topics*') ? ' active' : '' }}" href="/forums">
                <i class="app-menu__icon far fa-comment-alt"></i>
                <span class="app-menu__label">{{ __('index.forums') }}</span>
                <span class="badge badge-pill badge-light">{{ statsForum() }}</span>
            </a>
        </li>

        <li>
            <a class="app-menu__item{{ request()->is('guestbook*') ? ' active' : '' }}" href="/guestbook">
                <i class="app-menu__icon far fa-comment"></i>
                <span class="app-menu__label">{{ __('index.guestbook') }}</span>
                <span class="badge badge-pill badge-light">{{ statsGuestbook() }}</span>
            </a>
        </li>

        <li>
            <a class="app-menu__item{{ request()->is('news*') ? ' active' : '' }}" href="/news">
                <i class="app-menu__icon far fa-newspaper"></i>
                <span class="app-menu__label">{{ __('index.news') }}</span>
                <span class="badge badge-pill badge-light">{{ statsNews() }}</span>
            </a>
        </li>

        <li class="treeview{{ request()->is('blogs*', 'articles*') ? ' is-expanded' : '' }}">
            <a class="app-menu__item" href="#" data-toggle="treeview">
                <i class="app-menu__icon far fa-sticky-note"></i>
                <span class="app-menu__label">{{ __('index.blogs') }}</span>
                <i class="treeview-indicator fa fa-angle-down"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item{{ request()->is('blogs') ? ' active' : '' }}" href="/blogs"><i class="icon fas fa-circle fa-xs"></i> {{ __('blogs.blogs_list') }}</a></li>
                <li><a class="treeview-item{{ request()->is('blogs/main') ? ' active' : '' }}" href="/blogs/main"><i class="icon fas fa-circle fa-xs"></i> {{ __('blogs.articles') }}</a></li>
                <li><a class="treeview-item{{ request()->is('articles') ? ' active' : '' }}" href="/articles"><i class="icon fas fa-circle fa-xs"></i> {{ __('blogs.new_articles') }}</a></li>
                <li><a class="treeview-item{{ request()->is('articles/comments') ? ' active' : '' }}" href="/articles/comments"><i class="icon fas fa-circle fa-xs"></i> {{ __('blogs.new_comments') }}</a></li>
                <li><a class="treeview-item{{ request()->is('blogs/top') ? ' active' : '' }}" href="/blogs/top"><i class="icon fas fa-circle fa-xs"></i> {{ __('blogs.top_articles') }}</a></li>
            </ul>
        </li>
    </ul>

    <ul class="app-menu app-sidebar__footer">
        <li class="app-search search-sidebar mr-0">
            <form action="/search" method="get">
                <input name="q" class="app-search__input" type="search" placeholder="{{ __('main.search') }}" required>
                <button class="app-search__button"><i class="fa fa-search"></i></button>
            </form>
        </li>

        <li>
            <span class="float-right">
                @yield('online')
            </span>

            <i class="fas fa-globe-americas"></i>
            <a href="/language/ru{{ returnUrl() }}">RU</a> /
            <a href="/language/en{{ returnUrl() }}">EN</a>
        </li>
    </ul>
</aside>
