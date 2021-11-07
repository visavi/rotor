@if ($posts->isNotEmpty())
    @foreach ($posts as $post)
        @if ($post instanceof \App\Models\Topic && $post->lastPost->id)
            <div class="section mb-3 shadow">
                <h3><a class="post-title" href="/topics/{{ $post->id }}">{{ $post->title }}</a></h3>

                <div class="user-avatar">
                    {{ $post->lastPost->user->getAvatar() }}
                    {{ $post->lastPost->user->getOnline() }}
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        {{ $post->lastPost->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">{{ dateFixed($post->lastPost->created_at) }}</small>
                        <br>
                        <small class="fst-italic">{{ $post->lastPost->user->getStatus() }}</small>
                    </div>
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {{ $post->lastPost->text ? bbCode($post->lastPost->text) : 'Удалено' }}
                    </div>

                    @if ($post->lastPost->files->isNotEmpty())
                        <div class="section-media">
                            <i class="fa fa-paperclip"></i> <b>{{ __('main.attached_files') }}:</b><br>
                            @foreach ($post->files as $file)
                                <div class="media-file">
                                    {{ icons($file->extension) }}
                                    <a href="{{ $file->hash }}">{{ $file->name }}</a> ({{ formatSize($file->size) }})<br>
                                    @if ($file->isImage())
                                        <a href="{{ $file->hash }}" class="gallery" data-group="{{ $post->id }}">{{ resizeImage($file->hash, ['alt' => $file->name]) }}</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($post->edit_user_id)
                        <div class="small">
                            <i class="fa fa-exclamation-circle text-danger"></i> {{ __('main.changed') }}: {{ $post->editUser->getName() }} ({{ dateFixed($post->updated_at) }})
                        </div>
                    @endif

                    @if (isAdmin())
                        <div class="small text-muted fst-italic mt-2">{{ $post->brow }}, {{ $post->ip }}</div>
                    @endif
                </div>
            </div>
        @endif

        @if ($post instanceof \App\Models\Photo)
            <h3><a class="post-title" href="/photos/{{ $post->id }}">{{ $post->title }}</a></h3>
            @php
                $file = $post->files()->first();
            @endphp

            @if ($file)
                <a href="/photos/{{ $post->id }}">{{ resizeImage($file->hash, ['alt' => check($post->title), 'class' => 'rounded', 'style' => 'width: 100px;']) }}</a>
            @endif
        @endif

        @if ($post instanceof \App\Models\Down)
            <h3><a class="post-title" href="/downs/{{ $post->id }}">{{ $post->title }}</a></h3>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/downs/{{ $post->id }}">{{ $post->title }}</a> ({{ $post->count_comments }})<br>
        @endif

        @if ($post instanceof \App\Models\Article)
            <h3><a class="post-title" href="/articles/{{ $post->id }}">{{ $post->title }}</a></h3>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/articles/{{ $post->id }}">{{ $post->title }}</a> ({{ $post->count_comments }})<br>
        @endif

        @if ($post instanceof \App\Models\Item)
            <h3><a class="post-title" href="/items/{{ $post->id }}">{{ $post->title }}</a></h3>
            <i class="far fa-circle fa-lg text-muted"></i> <a href="/items/{{ $post->id }}">{{ $post->title }}</a><br>
        @endif
    @endforeach
@endif
