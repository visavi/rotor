@if ($news->isNotEmpty())
    <div class="section-body">
    @foreach ($news as $data)
        <i class="far fa-circle fa-lg text-muted"></i>
        <a href="/news/{{ $data->id }}">{{ $data->title }}</a> ({{ $data->count_comments }})
        <i class="fas fa-angle-down news-title cursor-pointer"></i><br>

        <div class="news-text" style="display: none;">
            {{ $data->shortText() }}
            <div>
                <a href="/news/comments/{{ $data->id }}">Комментарии</a>
                <a href="/news/end/{{ $data->id }}">&raquo;</a>
            </div>
        </div>
    @endforeach
    </div>
@endif
