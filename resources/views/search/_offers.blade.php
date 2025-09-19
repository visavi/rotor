<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-circle-question"></i> <a href="{{ route('offers.index') }}" class="text-muted">{{ __('index.offers') }}</a>
        </li>

        <li class="breadcrumb-item">
            @if ($post->type === 'offer')
                <a href="{{ route('offers.index', ['type' => 'offer']) }}" class="text-muted">{{ __('offers.offers') }}</a>
            @else
                <a href="{{ route('offers.index', ['type' => 'issue']) }}" class="text-muted">{{ __('offers.problems') }}</a>
            @endif
        </li>
    </ol>

    <div class="section-header d-flex align-items-start">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ route('offers.view', ['id' => $post->id]) }}">{{ $post->title }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content">
        <div class="section-message">
            {{ bbCodeTruncate($post->text, 100) }}
        </div>

        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>
</div>
