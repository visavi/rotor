@if ($articles)
    <div class="section-body">
    @foreach ($articles as $article)
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/articles/{{ $article['id'] }}">{{ $article['title'] }}</a> ({{ $article['count_comments'] }})<br>
    @endforeach
    </div>
@endif
