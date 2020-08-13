@if ($news)
    @foreach ($news as $data)
        <i class="far fa-circle fa-lg text-muted"></i> <a href="/news/{{ $data['id'] }}">{{ $data['title'] }}</a> ({{ $data['count_comments'] }}) <i class="fas fa-angle-down news-title"></i><br>

        @if (strpos($data['text'], '[cut]') !== false)
            @php
                $data['text'] = current(explode('[cut]', $data['text'])) . ' <a href="/news/'. $data['id'] .'" class="badge badge-success">Читать дальше &raquo;</a>';
            @endphp
        @endif

        <div class="news-text" style="display: none;">
            {!! bbCode($data['text']) !!}<br>
            <a href="/news/comments/{{ $data['id'] }}">Комментарии</a>
            <a href="/news/end/{{ $data['id'] }}">&raquo;</a>
        </div>
    @endforeach
@endif
