<nav>
	<ul class="pagination pagination-sm">
		@foreach($pages as $page)
			@if(isset($page['separator']))
				<li><span>{{ $page['name'] }}</span></li>
			@elseif(isset($page['current']))
				<li class="active"><span>{{ $page['name'] }}</span></li>
			@else
				<li><a href="?page={{ $page['page'] }}{{ $request }}">{{ $page['name'] }}</a></li>
			@endif
		@endforeach
	</ul>
</nav>
