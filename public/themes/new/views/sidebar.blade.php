<div class="app-sidebar">
    <div class="sidebar-header">

        @if ($user = getUser())
            <div class="">
                {!! $user->getAvatar() !!}
            </div>
            <div class="username">
                <span>{{ $user->getName() }}</span>
                <small>{!! $user->getStatus() !!}</small>
            </div>
        @else
            <a class="btn btn-info" href="/login{{ returnUrl() }}" rel="nofollow">Авторизация</a>
            <a class="btn btn-info" href="register" rel="nofollow">Регистрация</a>
        @endif
    </div>

    <div id="sidebar-nav" class="sidebar-nav">
        <ul>
            <li>
                <a href="/forums">
                    <span class="sidebar-nav-icon fa fa-comment"></span>
                    <span class="sidebar-nav-text">{{ __('index.forums') }}</span>
                    <span class="badge badge-pill badge-info">{{ statsForum() }}</span>
                </a>
            </li>
            <li>
                <a href="/guestbooks">
                    <span class="sidebar-nav-icon fa fa-comment"></span>
                    <span class="sidebar-nav-text">{{ __('index.guestbooks') }}</span>
                    <span class="badge badge-pill badge-info">{{ statsGuestbook() }}</span>
                </a>
            </li>
        </ul>
        @if ($user = getUser())
            <hr>
            <ul>
                <li>
                    <a href="/menu">
                        <span class="sidebar-nav-icon fa fa-list"></span>
                        <span class="sidebar-nav-text">Меню</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="sidebar-nav-icon fa fa-power-off"></span>
                        <span class="sidebar-nav-text">Log out</span>
                    </a>
                </li>
            </ul>
        @endif
    </div>
</div>
