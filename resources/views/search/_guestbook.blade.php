<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-note-sticky"></i> <a href="/guestbook" class="text-muted">{{ __('index.guestbook') }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="/guestbook">{{ __('main.message') }}</a></h3>
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
