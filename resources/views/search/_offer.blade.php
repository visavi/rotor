<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-circle-question"></i> <a href="/offers" class="text-muted">{{ __('index.offers') }}</a>
        </li>

        <li class="breadcrumb-item">
            @if ($post->type === 'offer')
                <a href="/offers/offer" class="text-muted">{{ __('offers.offers') }}</a>
            @else
                <a href="/offers/issue" class="text-muted">{{ __('offers.problems') }}</a>
            @endif
        </li>
    </ol>

    <div class="section-header d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="/offers/{{ $post->id }}">{{ $post->title }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content">
        <div class="section-message">
            {{ bbCodeTruncate($post->text, 100) }}
        </div>
    </div>
</div>
