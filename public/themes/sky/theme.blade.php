<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>@yield('title') - {{ setting('title') }}</title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    @yield('styles')
    @stack('styles')
    <link rel="stylesheet" href="/themes/sky/css/style.css" media="screen">
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml">
    <meta name="description" content="@yield('description', setting('description'))">
    <meta name="generator" content="Rotor {{ VERSION }}">
</head>
<body>
<!--Themes by TurikUs-->
    <div id="art-page-background-simple-gradient">
        <div id="art-page-background-gradient"></div>
    </div>
    <div id="art-page-background-glare">
        <div id="art-page-background-glare-image"></div>
    </div>
    <div id="art-main">
        <div class="art-Sheet">
            <div class="art-Sheet-tl"></div>
            <div class="art-Sheet-tr"></div>
            <div class="art-Sheet-bl"></div>
            <div class="art-Sheet-br"></div>
            <div class="art-Sheet-tc"></div>
            <div class="art-Sheet-bc"></div>
            <div class="art-Sheet-cl"></div>
            <div class="art-Sheet-cr"></div>
            <div class="art-Sheet-cc"></div>
            <div class="art-Sheet-body">
                <div class="art-nav">
                    <div class="l"></div>
                    <div class="r"></div>
                    <ul class="art-menu">
                        <li><a href="/"><span class="l"></span><span class="r"></span><span class="t">Главная</span></a></li>
                        <li><a href="/forums"><span class="l"></span><span class="r"></span><span class="t">Форум</span></a>
                            <ul>
                                 <li><a href="/topics">Новые темы</a></li>
                                 <li><a href="/forums/posts">Новые сообщения</a></li>
                            </ul>
                        </li>
                        <li><a href="/guestbooks"><span class="l"></span><span class="r"></span><span class="t">Гостевая</span></a></li>

                        <li><a href="/loads"><span class="l"></span><span class="r"></span><span class="t">Файлы</span></a>
                           <ul>
                                 <li><a href="/loads/new?act=files">Новые файлы</a></li>
                                 <li><a href="/loads/new?act=comments">Новые комментарии</a></li>
                           </ul>
                        </li>

                        <li><a href="/blogs"><span class="l"></span><span class="r"></span><span class="t">Блоги</span></a>
                           <ul>
                                 <li><a href="/articles">Новые статьи</a></li>
                                 <li><a href="/articles/comments">Новые комментарии</a></li>
                           </ul>
                        </li>

                        <li><a href="/photos"><span class="l"></span><span class="r"></span><span class="t">Галерея</span></a>
                           <ul>
                                 <li><a href="/photos/top">Топ фото</a></li>
                                 <li><a href="/photos/albums">Все альбомы</a></li>
                                            <li><a href="/photos/comments">Все комментарии</a></li>
                           </ul>
                        </li>

                        <li><a href="#"><span class="l"></span><span class="r"></span><span class="t">Актив сайта</span></a>
                           <ul>
                                 <li><a href="/administrators">Администрация</a></li>
                                 <li><a href="/users">Пользователи</a></li>
                           </ul>
                        </li>

                        @if (!getUser())
                            <li><a href="/register"><span class="l"></span><span class="r"></span><span class="t">{{ trans('index.register') }}</span></a></li>
                        @else
                            <li><a href="/logout?token={{ $_SESSION['token'] }}" onclick="return logout(this)"><span class="l"></span><span class="r"></span><span class="t">{{ trans('index.logout') }}</span></a></li>
                        @endif

            </ul></div>
                <div class="art-contentLayout">
                    <div class="art-sidebar1">
                        <div class="art-Block">
                            <div class="art-Block-tl"></div>
                            <div class="art-Block-tr"></div>
                            <div class="art-Block-bl"></div>
                            <div class="art-Block-br"></div>
                            <div class="art-Block-tc"></div>
                            <div class="art-Block-bc"></div>
                            <div class="art-Block-cl"></div>
                            <div class="art-Block-cr"></div>
                            <div class="art-Block-cc"></div>
                            <div class="art-Block-body">
                                <div class="art-BlockContent">
                                    <div class="art-BlockContent-body">
                                        <div>

                    @if (getUser())

                        @if (isAdmin())
                            <div class="nmenu">
                                <i class="fa fa-wrench"></i> <a href="/admin">{{ trans('index.panel') }}</a>

                                @if (statsSpam()>0)
                                    &bull; <a href="/admin/spam"><span style="color:#ff0000">Жалобы</span></a>
                                @endif

                                @if (getUser('newchat')<statsNewChat())
                                    &bull; <a href="/admin/chats"><span style="color:#ff0000">Чат</span></a>
                                @endif
                            </div>
                        @endif

                        @include('main/menu')
                    @else

                        <div class="divb">Авторизация</div>

                        <form method="post" action="/login{{ returnUrl() }}">
                        Логин:<br><input name="login"><br>
                        Пароль:<br><input name="pass" type="password"><br>
                        Запомнить меня:
                        <input name="remember" type="checkbox" value="1" checked><br>

                        <input value="Войти" type="submit"></form>

                        <a href="/register">{{ trans('index.register') }}</a><br>
                        <a href="/recovery">Забыли пароль?</a>
                    @endif


                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="art-Block">
                            <div class="art-Block-tl"></div>
                            <div class="art-Block-tr"></div>
                            <div class="art-Block-bl"></div>
                            <div class="art-Block-br"></div>
                            <div class="art-Block-tc"></div>
                            <div class="art-Block-bc"></div>
                            <div class="art-Block-cl"></div>
                            <div class="art-Block-cr"></div>
                            <div class="art-Block-cc"></div>
                            <div class="art-Block-body">
                                <div class="art-BlockContent">
                                    <div class="art-BlockContent-body">
                                        <div>
                                            <div class="divb">Календарь</div>
                                            {!! getCalendar() !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="art-Block">
                            <div class="art-Block-body">
                        <div class="art-BlockContent">


                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="art-content">
                        <div class="art-Post">
                            <div class="art-Post-tl"></div>
                            <div class="art-Post-tr"></div>
                            <div class="art-Post-bl"></div>
                            <div class="art-Post-br"></div>
                            <div class="art-Post-tc"></div>
                            <div class="art-Post-bc"></div>
                            <div class="art-Post-cl"></div>
                            <div class="art-Post-cr"></div>
                            <div class="art-Post-cc"></div>
                            <div class="art-Post-body">
                                <div class="art-Post-inner">

                                    <div class="art-PostMetadataHeader">
                                        @yield('note')
                                    </div>

                                    <h2 class="art-PostHeaderIcon-wrapper">
                                        <img src="/themes/sky/img/PostHeaderIcon.png" width="29" height="29" alt="PostHeaderIcon">
                                        <span class="art-PostHeader">{{ setting('title') }}</span>
                                    </h2>

                                    <div class="art-PostContent">

                                        <div>
                                            @yield('advertTop')
                                            @yield('advertUser')
                                            @yield('flash')
                                            @yield('breadcrumb')
                                            @yield('header')
                                            @yield('content')
                                            @yield('advertBottom')
                                        </div>
                                    </div>
                                    <div class="cleared"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="art-sidebar2">
                        <div class="art-Block">
                            <div class="art-Block-tl"></div>
                            <div class="art-Block-tr"></div>
                            <div class="art-Block-bl"></div>
                            <div class="art-Block-br"></div>
                            <div class="art-Block-tc"></div>
                            <div class="art-Block-bc"></div>
                            <div class="art-Block-cl"></div>
                            <div class="art-Block-cr"></div>
                            <div class="art-Block-cc"></div>
                            <div class="art-Block-body">
                                <div class="art-BlockContent">
                                    <div class="art-BlockContent-body">
                                        <div>
                                        @include('main/recent')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cleared"></div>
                <div class="art-Footer">
                    <div class="art-Footer-inner">
                        <a href="/news/rss" class="art-rss-tag-icon" title="RSS"></a>
                        <div class="art-Footer-text">
                        <a href="/">{{ setting('copy') }}</a><br>
                        @yield('online')
                        @yield('counter')
                        </div>

                        @yield('performance')
                    </div><div class="art-Footer-background"></div>
                </div>
            </div>
        </div>
    </div>

<div style="text-align:center"><small>
<a href="/faq">FAQ (Чаво)</a> |
<a href="/rules">Правила</a> |
<a href="/mails">Поддержка</a>
</small></div>
@yield('scripts')
@stack('scripts')
</body></html>
