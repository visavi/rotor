<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-solid fa-rectangle-list"></i> <a href="{{ route('boards.index') }}" class="text-muted">{{ __('index.boards') }}</a>
        </li>

        @if ($post->category->parent->id)
            <li class="breadcrumb-item">
                <a href="{{ route('boards.index', ['id' => $post->category->parent->id]) }}" class="text-muted">{{ $post->category->parent->name }}</a>
            </li>
        @endif

        <li class="breadcrumb-item">
            <a href="{{ route('boards.index', ['id' => $post->category->id]) }}" class="text-muted">{{ $post->category->name }}</a>
        </li>
    </ol>

    <h3><a class="post-title" href="{{ route('items.view', ['id' => $post->id]) }}">{{ $post->title }}</a></h3>

    <div class="section-content short-view col-md-12">
        @if ($post->files->isNotEmpty())
            <div class="row">
                <div class="col-md-12">
                    @include('app/_image_viewer', ['model' => $post])
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-10">
                <div class="section-message">
                    {{ $post->getText() }}
                </div>
            </div>

            <div class="col-md-2">
                @if ($post->price)
                    <div class="float-end">
                        <button type="button" class="btn btn-outline-info">{{ $post->price }} {{ setting('currency') }}</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="section-body">
        @if ($post->phone)
            <p class="card-text">
                <a href="tel:{{ $post->phone }}" class="text-decoration-none">
                    <i class="fa-solid fa-phone fs-5 me-2"></i> {{ $post->phone }}
                </a>
            </p>
        @endif

        <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->updated_at) }}</small>
    </div>
</div>
