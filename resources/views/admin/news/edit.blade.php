@extends('layout')

@section('title', __('news.edit_news_title') . ' ' . $news->title)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/news">{{ __('index.news') }}</a></li>
            <li class="breadcrumb-item"><a href="/news/{{ $news->id }}">{{ $news->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('news.edit_news_title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow cut">
        <form action="/admin/news/edit/{{ $news->id }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('title') }}">
                <label for="title" class="form-label">{{ __('main.title') }}:</label>
                <input type="text" class="form-control" id="title" name="title" maxlength="100" value="{{ getInput('title', $news->title) }}" placeholder="{{ __('main.title') }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="mb-3{{ hasError('text') }}">
                <label for="text" class="form-label">{{ __('main.text') }}:</label>
                <textarea class="form-control markItUp" maxlength="10000" id="text" rows="10" name="text" placeholder="{{ __('main.text') }}" required>{{ getInput('text', $news->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            @include('app/_upload_file', [
                'id'    => $news->id,
                'files' => $news->files,
                'type'  => $news->getMorphClass(),
            ])

            <div class="form-check">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="form-check-input" value="1" name="closed" id="closed"{{ getInput('closed', $news->closed) ? ' checked' : '' }}>
                <label for="closed" class="form-check-label">{{ __('main.close_comments') }}</label>
            </div>

            <div class="form-check">
                <input type="hidden" value="0" name="top">
                <input type="checkbox" class="form-check-input" value="1" name="top" id="top"{{ getInput('top', $news->top) ? ' checked' : '' }}>
                <label for="top" class="form-check-label">{{ __('news.pin_top') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
