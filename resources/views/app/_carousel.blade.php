<?php $countFiles = $model->files->count(); ?>
<div class="media-file">
    <div id="myCarousel{{ $model->id }}" class="carousel slide" data-ride="carousel">
        @if ($countFiles > 1)
            <ol class="carousel-indicators">
                @for ($i = 0; $i < $countFiles; $i++)
                    <li data-target="#myCarousel{{ $model->id }}" data-slide-to="{{ $i }}"{!! empty($i) ? ' class="active"' : '' !!}></li>
                @endfor
            </ol>
        @endif

        <div class="carousel-inner">
            @foreach ($model->files as $file)
                <div class="carousel-item{{ $loop->first ? ' active' : '' }}">

                    @php
                        $image = resizeImage($file->hash, ['alt' => $model->title]);

                        if (isset($path)) {
                            $image = '<a href="' . $path . '/' . $model->id . '">' . $image . '</a>';
                        }
                    @endphp

                    {!! $image !!}
                </div>
            @endforeach
        </div>

        @if ($countFiles > 1)
            <a class="carousel-control-prev" href="#myCarousel{{ $model->id }}" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#myCarousel{{ $model->id }}" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        @endif
    </div>
</div>
