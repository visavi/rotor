@extends('layout')

@section('title')
    {{ $down->title }}
@stop

@section('description', truncateDescription(bbCode($down->text, false)))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ trans('loads.title') }}</a></li>

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/loads/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/loads/{{ $down->category_id }}">{{ $down->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $down->title }}</li>
            <li class="breadcrumb-item"><a href="/downs/rss/{{ $down->id }}">{{ trans('main.rss') }}</a></li>

            @if (isAdmin('admin'))
                <li class="breadcrumb-item"><a href="/admin/downs/edit/{{ $down->id }}">{{ trans('main.edit') }}</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if (! $down->active)
        <div class="p-1 bg-warning text-dark">
            <i class="fas fa-exclamation-triangle"></i> {{ trans('loads.pending_down1') }}<br>
            @if ($down->user_id === getUser('id'))
                <i class="fa fa-pencil-alt"></i> <a href="/downs/edit/{{ $down->id }}">{{ trans('main.edit') }}</a>
            @endif
        </div><br>
    @endif

    <div class="message">
        {!! bbCode($down->text) !!}
    </div>

    @if ($down->files->isNotEmpty())
        @if ($down->getFiles()->isNotEmpty())
            <div class="mt-3">
                @foreach ($down->getFiles() as $file)
                    @if ($file->hash && file_exists(HOME . $file->hash))

                        @if ($file->extension === 'mp3')
                            <audio preload="none" controls style="max-width:100%;">
                                <source src="{{ $file->hash }}" type="audio/mp3">
                            </audio>
                        @endif

                        @if ($file->extension === 'mp4')
                            <?php $poster = file_exists(HOME . $file->hash . '.jpg') ? $file->hash . '.jpg' : null; ?>

                            <video width="640" height="360" style="max-width:100%;" poster="{{ $poster }}" preload="none" controls playsinline>
                                <source src="{{ $file->hash }}" type="video/mp4">
                            </video>
                        @endif

                        <b>{{ $file->name }}</b> ({{ formatSize($file->size) }})<br>
                        @if ($file->extension === 'zip')
                            <a href="/downs/zip/{{ $file->id }}">{{ trans('loads.view_archive') }}</a><br>
                        @endif

                        <a class="btn btn-success" href="/downs/download/{{ $file->id }}"><i class="fa fa-download"></i> {{ trans('main.download') }}</a><br>
                    @else
                        <i class="fa fa-download"></i> {{ trans('main.file_not_found') }}
                    @endif
                    <br>
                @endforeach
            </div>
        @endif

        @if ($down->getImages()->isNotEmpty())
            <div class="mt-2">
                @foreach ($down->getImages() as $image)
                    <a href="{{ $image->hash }}" class="gallery" data-group="{{ $down->id }}">{!! resizeImage($image->hash, ['alt' => $down->title]) !!}</a><br>
                @endforeach
            </div>
        @endif
    @else
        {!! showError(trans('main.not_uploaded')) !!}
    @endif

    <div class="mt-2">
        <i class="fa fa-comment"></i> <a href="/downs/comments/{{ $down->id }}">{{ trans('main.comments') }}</a> ({{ $down->count_comments }})
        <a href="/downs/end/{{ $down->id }}">&raquo;</a><br>

        {{ trans('main.rating') }}: {!! ratingVote($rating) !!}<br>
        {{ trans('main.votes') }}: <b>{{ $down->rated }}</b><br>
        {{ trans('main.downloads') }}: <b>{{ $down->loads }}</b><br>
        {{ trans('main.created') }}: {!! $down->user->getProfile() !!} ({{ dateFixed($down->created_at) }})<br><br>
    </div>

    @if (getUser() && getUser('id') !== $down->user_id)
        <form action="/downs/votes/{{ $down->id }}" method="post">
            @csrf
            <label for="score">{{ trans('main.your_vote') }}:</label>
            <div class="form-inline">
                <div class="form-group mb-2{{ hasError('score') }}">
                    <select class="form-control" id="score" name="score">
                        <option value="0">{{ trans('main.select_vote') }}</option>
                        <option value="1" {{ $down->vote === '1' ? ' selected' : '' }}>{{ trans('main.sucks') }}</option>
                        <option value="2" {{ $down->vote === '2' ? ' selected' : '' }}>{{ trans('main.bad') }}</option>
                        <option value="3" {{ $down->vote === '3' ? ' selected' : '' }}>{{ trans('main.normal') }}</option>
                        <option value="4" {{ $down->vote === '4' ? ' selected' : '' }}>{{ trans('main.good') }}</option>
                        <option value="5" {{ $down->vote === '5' ? ' selected' : '' }}>{{ trans('main.excellent') }}</option>
                    </select>
                    <div class="invalid-feedback">{{ textError('protect') }}</div>
                </div>
                <button class="btn btn-primary mb-2">{{ trans('main.rate') }}</button>
            </div>
        </form>
    @endif
@stop
