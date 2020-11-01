@if ($items)
    <div class="section-body">
    @foreach ($items as $item)
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/items/{{ $item['id'] }}">{{ $item['title'] }}</a><br>
    @endforeach
    </div>
@endif
