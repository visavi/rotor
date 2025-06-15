@if ($model->getImages()->isNotEmpty())
    @include('app/_image_viewer', ['model' => $model, 'files' => $model->getImages()])
@endif

@if ($model->getFiles()->isNotEmpty())
    <div class="section-media">
        <i class="fa fa-paperclip"></i> <b>{{ __('main.attached_files') }}:</b><br>
        @foreach ($model->getFiles() as $file)
            <div class="media-file">
                @if ($file->isVideo())
                    <div>
                        <video src="{{ $file->path }}" class="img-fluid rounded" preload="metadata" controls playsinline></video>
                    </div>
                @endif

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
