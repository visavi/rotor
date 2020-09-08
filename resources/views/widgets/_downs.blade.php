@if ($downs)
    <div class="section-body my-1 py-1">
    @foreach ($downs as $down)
        <i class="far fa-circle fa-lg text-muted"></i>  <a href="/downs/{{ $down['id'] }}">{{ $down['title'] }}</a> ({{ $down['count_comments'] }})<br>
    @endforeach
    </div>
@endif
