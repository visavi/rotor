<!-- Footer -->
<div class="app-footer">
    <footer class="row py-3 border-top">
        @hook('footerStart')
        <div class="col-sm">
            <h5>{{ __('index.pages') }}</h5>
            <ul class="nav flex-column">
                <li><a class="footer-item" href="{{ route('pages') }}">{{ __('index.pages') }}</a></li>
                @hook('footerColumnStart')
            </ul>
        </div>

        <div class="col-sm">
            <h5>{{ __('index.users') }}</h5>
            <ul class="nav flex-column">
                <li><a class="footer-item" href="{{ route('users.index') }}">{{ __('index.users') }}</a> <span class="badge bg-adaptive">{{ statsUsers() }}</span></li>
                @hook('footerColumnMiddle')
            </ul>
        </div>

        <div class="col-sm">
            <h5>{{ __('index.information') }}</h5>
            <ul class="nav flex-column">
                <li><a class="footer-item" href="{{ route('search') }}">{{ __('index.search') }}</a></li>
                @hook('footerColumnEnd')
            </ul>
        </div>

        <div class="col-12 py-3">
            {{ showCounter() }}

            <div class="float-end">
                @include('app/_social')
            </div>

            <div class="text-muted">{{ setting('copy') }}</div>
        </div>
        @hook('footerEnd')
    </footer>

    {{ performance() }}
</div>
