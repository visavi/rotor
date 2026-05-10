@if ($model->getDetachedMedia()->isNotEmpty())
    @include('app/_media_slider', ['model' => $model, 'files' => $model->getDetachedMedia()])
@endif

@if ($model->getFiles()->isNotEmpty())
    <div class="section-media">
        <i class="fa fa-paperclip"></i> <b>{{ __('main.attached_files') }}:</b><br>
        @foreach ($model->getFiles() as $file)
            <div class="media-file">
                @if ($file->isAudio())
                    <div>
                        <audio src="{{ $file->path }}" class="img-fluid rounded" preload="metadata" controls></audio>
                    </div>
                @endif

                {{ icons($file->extension) }}
                <a href="{{ $file->path }}">{{ $file->name }}</a> ({{ formatSize($file->size) }})
            </div>
        @endforeach
    </div>
@endif
