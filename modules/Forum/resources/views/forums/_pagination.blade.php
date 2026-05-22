<div>{{ __('main.pages') }}:
    @foreach ($pages as $page)
        @if (isset($page['separator']))
            <span>{{ $page['name'] }}</span>
        @else
            <a href="{{ $page['url'] }}">{{ $page['name'] }}</a>
        @endif
    @endforeach
</div>
