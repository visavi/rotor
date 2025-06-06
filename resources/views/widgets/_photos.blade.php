@if ($photos->isNotEmpty())
    <div class="section-body">
    @foreach ($photos as $photo)
        @php
            $file = $photo->files()->first();
        @endphp

        @if ($file)
            <a href="{{ route('photos.view', ['id' => $photo->id]) }}">{{ resizeImage($file->path, ['alt' => check($photo->title), 'class' => 'rounded', 'style' => 'width: 100px;']) }}</a>
        @endif
    @endforeach
    </div>
@endif
