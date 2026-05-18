<div class="text-center">
    {!! $result !!}

    @if (getUser())
        @if ($result)
            <small><a href="/adverts" rel="nofollow">[+]</a></small>
        @else
            <small><a href="/adverts/create" rel="nofollow">{{ __('adverts.create_advert') }}</a></small>
        @endif
    @endif
</div>
