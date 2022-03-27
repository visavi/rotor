@extends('layout')

@section('title', $down->title)

@section('description', truncateDescription(bbCode($down->text, false)))

@section('header')
    @if (isAdmin('admin'))
        <div class="float-end">
            <a class="btn btn-light" href="/admin/downs/edit/{{ $down->id }}"><i class="fas fa-wrench"></i></a>
        </div>
    @endif

    <h1>{{ $down->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ __('index.loads') }}</a></li>

            @foreach ($down->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="/loads/{{ $parent->id }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item active">{{ $down->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (! $down->active)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> {{ __('loads.pending_down1') }}<br>
            @if (getUser() && getUser('id') === $down->user_id)
                <i class="fa fa-pencil-alt"></i> <a href="/downs/edit/{{ $down->id }}">{{ __('main.edit') }}</a>
            @endif
        </div>
    @endif

    <i class="fas fa-rss"></i> <a class="me-3" href="/downs/rss/{{ $down->id }}">{{ __('main.rss') }}</a>
    <hr>

    <div class="mb-3">
        @if ($down->getImages()->isNotEmpty())
            @foreach ($down->getImages() as $image)
                <div class="media-file mb-3">
                    <a href="{{ $image->hash }}" class="gallery" data-group="{{ $down->id }}">{{ resizeImage($image->hash, ['alt' => $down->title]) }}</a>
                </div>
            @endforeach
        @endif

        <div class="section-message mb-3">
            {{ bbCode($down->text) }}
        </div>

        @if ($down->links || $down->files->isNotEmpty())
            @foreach ($down->getFiles() as $file)
                <div class="media-file mb-3">
                    @if ($file->hash && file_exists(public_path($file->hash)))
                        @if ($file->extension === 'mp3')
                            <div>
                                <audio src="{{ $file->hash }}" style="max-width:100%;" preload="metadata" controls controlsList="{{ $allowDownload ? null : 'nodownload' }}"></audio>
                            </div>
                        @endif

                        @if ($file->extension === 'mp4')
                            <div>
                                <video src="{{ $file->hash }}" style="max-width:100%;" preload="metadata" controls playsinline controlsList="{{ $allowDownload ? null : 'nodownload' }}"></video>
                            </div>
                        @endif

                        <b>{{ $file->name }}</b> ({{ formatSize($file->size) }})<br>
                        @if ($file->extension === 'zip')
                            <a href="/downs/zip/{{ $file->id }}">{{ __('loads.view_archive') }}</a><br>
                        @endif

                        @if ($allowDownload)
                            <a class="btn btn-success" href="/downs/download/{{ $file->id }}"><i class="fa fa-download"></i> {{ __('main.download') }}</a><br>
                        @endif
                    @else
                        <i class="fa fa-download"></i> {{ __('main.file_not_found') }}
                    @endif
                </div>
            @endforeach

            @if ($down->links && $allowDownload)
                @foreach ($down->links as $linkId => $link)
                    <div class="media-file mb-3">
                        <b>{{ basename($link) }}</b><br>
                        <a class="btn btn-success" href="/downs/download/{{ $down->id }}/{{ $linkId }}"><i class="fa fa-download"></i> {{ __('main.download') }}</a>
                    </div>
                @endforeach
            @endif

            @if (! $allowDownload)
                {{ showError(__('loads.download_authorized')) }}
            @endif
        @else
            {{ showError(__('main.not_uploaded')) }}
        @endif

        <div class="mb-3">
            <i class="fa fa-comment"></i> <a href="/downs/comments/{{ $down->id }}">{{ __('main.comments') }}</a> ({{ $down->count_comments }})
            <a href="/downs/end/{{ $down->id }}">&raquo;</a><br>

            <div class="my-2 js-rating">{{ __('main.rating') }}:
                @if (getUser() && getUser('id') !== $down->user_id)
                    <a class="post-rating-down<?= $down->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $down->id }}" data-type="{{ $down->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-down"></i></a>
                @endif
                <b>{{ formatNum($down->rating) }}</b>
                @if (getUser() && getUser('id') !== $down->user_id)
                    <a class="post-rating-up<?= $down->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $down->id }}" data-type="{{ $down->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-up"></i></a>
                @endif
            </div>

            {{ __('main.downloads') }}: <b>{{ $down->loads }}</b><br>
            {{ __('main.author') }}: {{ $down->user->getProfile() }} ({{ dateFixed($down->created_at) }})
        </div>
    </div>
@stop
