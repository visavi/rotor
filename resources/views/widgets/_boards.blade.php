@if ($items)
    <div class="section-body my-1 py-1">
    @foreach ($items as $item)
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/items/{{ $item['id'] }}">{{ $item['title'] }}</a><br>
    @endforeach
    </div>
@endif
