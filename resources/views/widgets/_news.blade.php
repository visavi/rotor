@if ($news->isNotEmpty())
    <div class="section-body">
    @foreach ($news as $data)
        <i class="far fa-circle text-muted"></i>
            <a href="{{ route('news.index', ['id' => $data->id]) }}">{{ $data->title }}</a> <span class="badge bg-adaptive">{{ $data->count_comments }}</span>
        <i class="fas fa-angle-down news-title cursor-pointer"></i><br>

        <div class="news-text" style="display: none;">
            {{ $data->shortText() }}
            <div>
                <a href="{{ route('news.comments', ['id' => $data->id]) }}">Комментарии</a>
            </div>
        </div>
    @endforeach
    </div>
@endif
