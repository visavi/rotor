@if ($topics->isNotEmpty())
    <div class="section-body">
    @foreach ($topics as $topic)
            <i class="far fa-circle text-muted"></i>  <a href="{{ route('topics.topic', ['id' => $topic->id]) }}">{{ $topic->title }}</a> <span class="badge bg-adaptive">{{ $topic->count_posts }}</span>
    @endforeach
    </div>
@endif
