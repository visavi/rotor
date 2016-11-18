<div>Страницы:
	@foreach($pages as $page)
		@if(isset($page['separator']))
			<span>{{ $page['name'] }}</span>
		@else
			<a href="{{ $link }}?page={{ $page['page'] }}" data-toggle="tooltip" title="{{ $page['title'] }}">{{ $page['name'] }}</a>
		@endif
	@endforeach
</div>
