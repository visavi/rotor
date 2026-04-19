<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-regular fa-image"></i> <a href="{{ route('photos.index') }}" class="text-muted">{{ __('index.photos') }}</a>
        </li>
    </ol>

    <div class="section-header d-flex align-items-start">
        <div class="flex-grow-1">
            <div class="section-title">
                <h3><a class="post-title" href="{{ route('photos.view', ['id' => $post->id]) }}">{{ $post->title }}</a></h3>
            </div>
        </div>
    </div>

    <div class="section-content short-view">
        {{--@include('app/_image_viewer', ['model' => $post])--}}

        <div class="section-message">
            {{ $post->text ? renderHtml($post->text) : __('main.deleted') }}
        </div>

        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </div>
</div>
