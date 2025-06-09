<!-- Footer -->
<div class="app-footer">
    <footer class="row py-3 border-top">
        @hook('footerStart')
        <div class="col-sm">
            <h5>{{ __('index.pages') }}</h5>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="/pages">{{ __('index.pages') }}</a></li>
                <li class="nav-item mb-2"><a href="/files/docs">{{ __('index.docs') }}</a></li>
                @hook('footerColumnStart')
            </ul>
        </div>

        <div class="col-sm">
            <h5>{{ __('index.users') }}</h5>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="/users">{{ __('index.users') }}</a> ({{  statsUsers() }})</li>
                <li class="nav-item mb-2"><a href="{{ route('offers.index') }}">{{ __('index.offers') }}</a> ({{ statsOffers() }})</li>
                @hook('footerColumnMiddle')
            </ul>
        </div>

        <div class="col-sm">
            <h5>{{ __('index.mails') }}</h5>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="/mails">{{ __('index.mails') }}</a></li>
                <li class="nav-item mb-2"><a href="/search">{{ __('index.search') }}</a></li>
                @hook('footerColumnEnd')
            </ul>
        </div>

        <div class="col-12 py-3">
            @yield('counter')

            <div class="float-end">
                <a target="_blank" href="https://telegram.me/visavinet"><i class="fab fa-telegram fa-2x" style="color: #0088cc"></i></a>
                <a target="_blank" href="https://vk.com/visavinet"><i class="fab fa-vk fa-2x" style="color: #45668e"></i></a>
                <a target="_blank" href="https://www.facebook.com/groups/visavinet"><i class="fab fa-facebook-square fa-2x" style="color: #3b5998"></i></a>
            </div>

            <div class="text-muted">{{ setting('copy') }}</div>
        </div>
        @hook('footerEnd')
    </footer>

    @yield('performance')
</div>
