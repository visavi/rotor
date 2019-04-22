<!DOCTYPE html>
<html lang="{{ setting('language') }}">
<head>
    <title>@yield('title') - {{ setting('title') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>
    @yield('styles')
    @stack('styles')
    <link rel="stylesheet" href="/themes/toonel/css/style.css"/>
    <link rel="alternate" href="/news/rss" title="RSS News" type="application/rss+xml"/>
    <meta name="keywords" content="%KEYWORDS%"/>
    <meta name="description" content="%DESCRIPTION%"/>
    <meta name="generator" content="Rotor {{ VERSION }}"/>
</head>
<body>
<!--Design by Vantuz (http://pizdec.ru)-->
<table border="0" align="center" cellpadding="0" cellspacing="0" class="submenu" id="up">
    <tr>
        <td class="t1">
            <a href="/">
                <img src="/themes/toonel/img/logo.gif" alt="{{ setting('title') }}"/>
            </a>
        </td>
        <td class="t2"></td>
        <td class="t3">
            <a title="Центр общения" class="menu" href="/forums">Форум</a> |
            <a title="Гостевая комната" class="menu" href="/guestbooks">Гостевая</a> |
            <a title="Загрузки" class="menu" href="/loads">Загрузки</a> |
            @if (getUser())
                <a title="Настройки" class="menu" href="/menu">Меню</a> |
                <a title="Выход" class="menu" href="/logout?token={{ $_SESSION['token'] }}" onclick="return logout(this)">Выход</a>
            @else
                <a title="Страница авторизации" class="menu" href="/login">Вход</a> |
                <a title="Страница регистрации" class="menu"
                   href="/register">{{ trans('index.register') }}</a>
            @endif
        </td>
        <td class="t4"></td>
    </tr>
</table>

<table border="0" align="center" cellpadding="0" cellspacing="0" class="tab2">
    <tr>
        <td align="left" valign="top" class="leftop">
        </td>
        <td class="bortop"></td>
        <td align="right" valign="top" class="righttop"></td>
    </tr>
    <tr>
        <td class="left_mid">&nbsp;</td>
        <td valign="top" class="lr">
            @if (isAdmin())
                <div class="nmenu">
                    <i class="fa fa-wrench"></i> <a
                        href="/admin">Панель</a>

                    @if (statsSpam() > 0)
                        &bull; <a href="/admin/spam"><span style="color:#ff0000">Жалобы</span></a>
                    @endif

                    @if (getUser('newchat') < statsNewChat())
                        &bull; <a href="/admin/chats"><span style="color:#ff0000">Чат</span></a>
                    @endif
                </div>
            @endif
            <div class="content">
                @yield('advertTop')
                @yield('advertUser')
                @yield('note')
                @yield('flash')
                @yield('breadcrumb')
                @yield('header')
                @yield('content')
                @yield('advertBottom')
            </div>

        </td>
        <td class="right_mid">&nbsp;</td></tr>
    <tr>
        <td align="left" valign="top" class="lefbot"></td>
        <td class="borbottom"></td>
        <td align="right" valign="top" class="rightbot"></td>
    </tr>
</table>

<table class="tab2" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td width="120" valign="top" class="fottopleft"></td>
        <td class="ftop"></td>
        <td width="120" valign="top" class="fottopright"></td>
    </tr>

    <tr>
        <td align="center" colspan="3" class="ftexttd">
            @yield('counter')
            @yield('online')
            <a href="/">{{ setting('copy') }}</a><br/>
        </td>
    </tr>

    <tr>
        <td valign="top" class="footer_left"></td>
        <td valign="top" class="fbottom"></td>
        <td valign="top" class="footer_right"></td>
    </tr>
</table>

<table class="tab2" align="center">
    <tr>
        <td align="center">
            @yield('performance')
        </td>
    </tr>
</table>
@yield('scripts')
@stack('scripts')
</body></html>
