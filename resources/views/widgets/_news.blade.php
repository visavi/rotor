@if ($news)
    <div class="section-body my-1 py-1">
    @foreach ($news as $data)
        <i class="far fa-circle fa-lg text-muted"></i>
        <a href="/news/{{ $data['id'] }}">{{ $data['title'] }}</a> ({{ $data['count_comments'] }})
        <i class="fas fa-angle-down news-title cursor-pointer"></i><br>

        <?php
        $more = null;
        if (strpos($data['text'], '[cut]') !== false) :
            $more = view('app/_more', ['link' => '/news/'. $data['id']]);
            $data['text'] = trim(current(explode('[cut]', $data['text'])));
        endif;
        ?>

        <div class="news-text" style="display: none;">
            {!! bbCode($data['text']) !!} {!! $more !!}
            <div>
                <a href="/news/comments/{{ $data['id'] }}">Комментарии</a>
                <a href="/news/end/{{ $data['id'] }}">&raquo;</a>
            </div>
        </div>
    @endforeach
    </div>
@endif
