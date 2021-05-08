@extends('layout')

@section('title', __('guestbook.title_reply'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/guestbook">{{ __('index.guestbook') }}</a></li>
            <li class="breadcrumb-item active">{{ __('guestbook.title_reply') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="alert alert-info">
        <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->getName() }}</b>
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small><br>
        <div>{{ __('main.message') }}: {{ bbCode($post->text) }}</div>
    </div>

    <div class="section-form mb-3 shadow">
        <form action="/admin/guestbook/reply/{{ $post->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('reply') }}">
                <label for="reply" class="form-label">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" id="reply" rows="5" name="reply" required>{{ getInput('reply', $post->reply) }}</textarea>
                <div class="invalid-feedback">{{ textError('reply') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.write') }}</button>
        </form>
    </div>
@stop
