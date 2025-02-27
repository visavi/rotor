<div class="section mb-3 shadow">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
            <i class="fa-solid fa-rectangle-list"></i> <a href="/boards" class="text-muted">{{ __('index.boards') }}</a>
        </li>

        @if ($post->category->parent->id)
            <li class="breadcrumb-item">
                <a href="/boards/{{ $post->category->parent->id }}" class="text-muted">{{ $post->category->parent->name }}</a>
            </li>
        @endif

        <li class="breadcrumb-item">
            <a href="/boards/{{ $post->category->id }}" class="text-muted">{{ $post->category->name }}</a>
        </li>
    </ol>

    <h3><a class="post-title" href="/items/{{ $post->id }}">{{ $post->title }}</a></h3>

    <div class="col-md-12">
        @if ($post->files->isNotEmpty())
            <div class="row">
                <div class="col-md-12">
                    @include('app/_carousel', ['model' => $post])
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-10">
                <div class="section-message">
                    {{ bbCode($post->text) }}
                </div>
                <div>
                    @if ($post->phone)
                        <span class="badge rounded-pill bg-primary mb-3">{{ __('boards.phone') }}: <a href="tel:{{ $post->phone }}">{{ $post->phone }}</a></span><br>
                    @endif

                    <span class="avatar-micro">{{ $post->user->getAvatarImage() }}</span> {{ $post->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">{{ dateFixed($post->updated_at) }}</small>
                </div>
            </div>

            <div class="col-md-2">
                @if ($post->price)
                    <button type="button" class="btn btn-sm btn-light">{{ $post->price }} {{ setting('currency') }}</button>
                @endif
            </div>
        </div>
    </div>
</div>
