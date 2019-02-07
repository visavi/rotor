<div>{{ trans('common.pages') }}:
    @foreach ($pages as $page)
        @if (isset($page['separator']))
            <span>{{ $page['name'] }}</span>
        @else
            <a href="{{ $link }}?page={{ $page['page'] }}">{{ $page['name'] }}</a>
        @endif
    @endforeach
</div>
