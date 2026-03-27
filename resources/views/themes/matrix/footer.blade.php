<div class="wap-hr"></div>
<footer id="footer">
    <span>
        @yield('online')
        <a href="{{ route('language', ['lang' => 'ru']) }}{{ returnUrl() }}">RU</a>/<a href="{{ route('language', ['lang' => 'en']) }}{{ returnUrl() }}">EN</a>
    </span>
    <span class="sep">|</span>
    <a href="{{ route('users.index') }}">{{ __('index.users') }}({{ statsUsers() }})</a>
    <span class="sep">|</span>
    <a href="{{ route('pages') }}">{{ __('index.pages') }}</a>
    <span class="sep">|</span>
    <a href="{{ route('search') }}">{{ __('index.search') }}</a>
    <span class="sep">|</span>
    <a href="{{ route('offers.index') }}">{{ __('index.offers') }}</a>
    @hook('footerStart')
    @hook('footerColumnStart')
    @hook('footerColumnMiddle')
    @hook('footerColumnEnd')
    @hook('footerEnd')
    <div id="copy">{{ setting('copy') }}</div>
    @yield('performance')
</footer>
