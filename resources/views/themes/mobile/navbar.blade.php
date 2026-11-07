<div class="cs" id="up">
    {{--<a href="{{ route('home') }}"><span class="logotype">{{ setting('title') }}</span></a><br>--}}
    <a href="{{ route('home') }}"><img src="{{ setting('logotip') }}" alt="{{ setting('title') }}"></a><br>
    {{ setting('logos') }}
</div>

<div class="menu">
    @include('themes/mobile/menu')

    <div class="float-end d-flex align-items-center gap-2">
        <ul class="hooks-nav">
            @hook('navbarStart')
            @hook('navbarEnd')
        </ul>
        @if (($user = auth()->user()) && $user->isActive())
            <div>
                <a class="d-flex align-items-center gap-1" href="{{ route('messages.index') }}" aria-label="{{ __('index.private_message') }}"><i class="far fa-envelope fa-lg"></i><span class="badge rounded-pill bg-danger js-message-count">{{ $user->getCountNewMessages() ?: '' }}</span></a>
            </div>
        @endif
        <div>
            <a href="#" data-dialog="#languageDialog"><img src="/assets/flags/{{ app()->getLocale() }}.svg" alt="" width="22" class="me-1 flag" onerror="this.remove()"> {{ __('main.lang') }}</a>
        </div>
    </div>
</div>
