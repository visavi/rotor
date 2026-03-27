<div class="wh-hr"></div>
<footer id="footer">
    @hook('footerStart')
    <div class="c">
        @yield('online')
        <a href="{{ route('users.index') }}">{{ __('index.users') }}</a>(<small>{{ statsUsers() }}</small>)
        &nbsp;|&nbsp;
        <a href="{{ route('pages') }}">{{ __('index.pages') }}</a>
        &nbsp;|&nbsp;
        <a href="{{ route('search') }}">{{ __('index.search') }}</a>
        &nbsp;|&nbsp;
        <a href="{{ route('offers.index') }}">{{ __('index.offers') }}</a>
        &nbsp;|&nbsp;
        <a href="{{ route('language', ['lang' => 'ru']) }}{{ returnUrl() }}">RU</a>/<a href="{{ route('language', ['lang' => 'en']) }}{{ returnUrl() }}">EN</a>
        @hook('footerColumnStart')
        @hook('footerColumnMiddle')
        @hook('footerColumnEnd')
    </div>
    @hook('footerEnd')
    <div id="copy"><small>{{ setting('copy') }}</small></div>
    @yield('performance')
</footer>
