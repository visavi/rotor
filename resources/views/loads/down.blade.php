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

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/loads/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/loads/{{ $down->category_id }}">{{ $down->category->name }}</a></li>
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

        @if ($down->getFiles()->isNotEmpty())
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

            @if (! $allowDownload)
                {{ showError(__('loads.download_authorized')) }}
            @endif
        @else
            {{ showError(__('main.not_uploaded')) }}
        @endif

        <div class="mb-3">
            <i class="fa fa-comment"></i> <a href="/downs/comments/{{ $down->id }}">{{ __('main.comments') }}</a> ({{ $down->count_comments }})
            <a href="/downs/end/{{ $down->id }}">&raquo;</a><br>

            {{ __('main.rating') }}: {{ ratingVote($down->getCalculatedRating()) }}<br>
            {{ __('main.votes') }}: <b>{{ $down->rated }}</b><br>
            {{ __('main.downloads') }}: <b>{{ $down->loads }}</b><br>
            {{ __('main.author') }}: {{ $down->user->getProfile() }} ({{ dateFixed($down->created_at) }})
        </div>

        @if (getUser() && getUser('id') !== $down->user_id)
            <div class="row ">
                <form action="/downs/votes/{{ $down->id }}" method="post" class="col-lg-4 col-md-6 col-sm-6">
                    @csrf
                    <label for="score" class="form-label">{{ __('main.your_vote') }}:</label>
                    <div class="input-group{{ hasError('score') }}">
                        <select class="form-select" id="score" name="score">
                            <option value="0">{{ __('main.select_vote') }}</option>
                            <option value="1" {{ $down->vote === '1' ? ' selected' : '' }}>{{ __('main.sucks') }}</option>
                            <option value="2" {{ $down->vote === '2' ? ' selected' : '' }}>{{ __('main.bad') }}</option>
                            <option value="3" {{ $down->vote === '3' ? ' selected' : '' }}>{{ __('main.normal') }}</option>
                            <option value="4" {{ $down->vote === '4' ? ' selected' : '' }}>{{ __('main.good') }}</option>
                            <option value="5" {{ $down->vote === '5' ? ' selected' : '' }}>{{ __('main.excellent') }}</option>
                        </select>
                        <button class="btn btn-primary">{{ $down->vote ? __('main.change') : __('main.rate') }}</button>
                    </div>
                    <div class="invalid-feedback">{{ textError('protect') }}</div>
                </form>
            </div>
        @endif
    </div>
@stop
