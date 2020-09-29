<!-- Sidebar menu-->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar">
    <ul class="app-menu cursor-pointer">
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
                            <i class="app-menu__icon fas fa-wrench"></i>
                            {{ __('index.panel') }}
                        </a>
                    </li>
                @endif
                <li>
                    <a class="treeview-item" href="/menu" rel="nofollow">
                        <i class="app-menu__icon fas fa-user-cog"></i>
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
                        <i class="app-menu__icon fas fa-sign-in-alt"></i>
                        {{ __('index.login') }}
                    </a>
                </li>
                <li>
                    <a class="treeview-item" href="/register" rel="nofollow">
                        <i class="app-menu__icon far fa-user"></i>
                        {{ __('index.register') }}
                    </a>
                </li>
            </ul>
        @endif
        </li>
    </ul>

    @if ($user = getUser())
{{--        <div class="app-sidebar__user" data-target="#collapseLogin" data-toggle="collapse">
            <div class="app-sidebar__user-avatar">
                {!! $user->getAvatarImage() !!}
            </div>
            <div>
                <p class="app-sidebar__user-name">{{ $user->getName() }}</p>
                <p class="app-sidebar__user-designation">{!! $user->getStatus() !!}</p>
            </div>
        </div>

        <div class="collapse" id="collapseLogin">
            <ul class="app-sidebar__menu">
                @if (isAdmin())
                <li>
                    <a href="/admin" rel="nofollow">
                        <i class="app-menu__icon fas fa-wrench"></i>
                        <span class="app-menu__label">{{ __('index.panel') }}</span>
                    </a>
                </li>
                @endif
                <li>
                    <a href="/menu" rel="nofollow">
                        <i class="app-menu__icon fas fa-user-cog"></i>
                        <span class="app-menu__label ">{{ __('index.menu') }}</span>
                    </a>
                </li>
            </ul>
        </div>--}}
    @else
{{--        <div class="app-sidebar__user" data-target="#collapseLogin" data-toggle="collapse">
            <div class="app-sidebar__user-avatar">
                <img class="img-fluid rounded-circle avatar-default" src="/assets/img/images/avatar_guest.png" alt="">
            </div>
            <div>
                <p class="app-sidebar__user-name">{{ __('users.enter') }}</p>
            </div>
        </div>

        <div class="collapse" id="collapseLogin">
            <ul class="app-sidebar__menu">
                <li>
                    <a href="/login{{ returnUrl() }}" rel="nofollow">
                        <i class="app-menu__icon fas fa-sign-in-alt"></i>
                        <span class="app-menu__label">{{ __('index.login') }}</span>
                    </a>
                </li>
                <li>
                    <a href="/register" rel="nofollow">
                        <i class="app-menu__icon far fa-user"></i>
                        <span class="app-menu__label">{{ __('index.register') }}</span>
                    </a>
                </li>
            </ul>
        </div>--}}
    @endif

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
                <li><a class="treeview-item{{ request()->is('blogs') ? ' active' : '' }}" href="/blogs"><i class="icon far fa-circle"></i> {{ __('blogs.blogs_list') }}</a></li>
                <li><a class="treeview-item{{ request()->is('blogs/main') ? ' active' : '' }}" href="/blogs/main"><i class="icon far fa-circle"></i> {{ __('blogs.articles') }}</a></li>
                <li><a class="treeview-item{{ request()->is('articles') ? ' active' : '' }}" href="/articles"><i class="icon far fa-circle"></i> {{ __('blogs.new_articles') }}</a></li>
                <li><a class="treeview-item{{ request()->is('articles/comments') ? ' active' : '' }}" href="/articles/comments"><i class="icon far fa-circle"></i> {{ __('blogs.new_comments') }}</a></li>
                <li><a class="treeview-item{{ request()->is('blogs/top') ? ' active' : '' }}" href="/blogs/top"><i class="icon far fa-circle"></i> {{ __('blogs.top_articles') }}</a></li>
            </ul>
        </li>
    </ul>
</aside>
