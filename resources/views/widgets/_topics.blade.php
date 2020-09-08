@if ($topics)
    <div class="section-body my-1 py-1">
    @foreach ($topics as $topic)
        <i class="far fa-circle fa-lg text-muted"></i>  <a href="/topics/{{ $topic['id'] }}">{{ $topic['title'] }}</a> ({{ $topic['count_posts'] }})
        <a href="/topics/end/{{ $topic['id'] }}">&raquo;</a><br>
    @endforeach
    </div>
@endif
