<nav>
	<ul class="pagination">
		@foreach($pages as $page)
			@if(isset($page['separator']))
				<li><span>{{ $page['name'] }}</span></li>
			@elseif(isset($page['current']))
				<li class="active"><span data-toggle="tooltip" title="Текущая">{{ $page['name'] }}</span></li>
			@else
				<li><a href="?page={{ $page['page'] }}{{ $request }}" data-toggle="tooltip" title="{{ $page['title'] }}">{{ $page['name'] }}</a></li>
			@endif
		@endforeach
	</ul>
</nav>
