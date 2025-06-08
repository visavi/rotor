@if ($articles->isNotEmpty())
    <div class="section-body">
    @foreach ($articles as $article)
        <i class="far fa-circle fa-lg text-muted"></i> <a href="{{ route('articles.view', ['id' => $article->id]) }}">{{ $article->title }}</a> ({{ $article->count_comments }})<br>
    @endforeach
    </div>
@endif
