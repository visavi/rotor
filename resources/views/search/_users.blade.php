<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-note-sticky"></i> <a href="/users" class="text-muted">{{ __('index.users') }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-start">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="/users/{{ $post->login }}">{{ $post->login }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content">
        <div class="section-message">
            <div class="user-avatar">
                {{ $post->getAvatar() }}
                {{ $post->getOnline() }}
            </div>

            <div class="section-user d-flex align-items-start">
                <div class="flex-grow-1">
                    {{ $post->getProfile() }}
                    <small class="fst-italic">{{ $post->getStatus() }}</small>

                    @if ($post->info)
                        <div>{{ bbCodeTruncate($post->info, 100) }}</div>
                    @endif

                    @if ($post->site)
                        <div><i class="fa fa-home"></i> <a href="{{ $post->site }}">{{ $post->site }}</a></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
