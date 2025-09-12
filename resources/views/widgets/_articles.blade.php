@if ($articles->isNotEmpty())
    <div class="section-body">
    @foreach ($articles as $article)
            <i class="far fa-circle text-muted"></i> <a href="{{ route('articles.view', ['slug' => $article->slug]) }}">{{ $article->title }}</a> <span class="badge bg-adaptive">{{ $article->count_comments }}</span><br>
    @endforeach
    </div>
@endif
