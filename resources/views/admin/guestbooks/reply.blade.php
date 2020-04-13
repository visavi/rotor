@extends('layout')

@section('title')
    {{ __('guestbooks.title_reply') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/guestbooks">{{ __('index.guestbooks') }}</a></li>
            <li class="breadcrumb-item active">{{ __('guestbooks.title_reply') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="alert alert-info">
        <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br>
        <div>{{ __('main.message') }}: {!! bbCode($post->text) !!}</div>
    </div>

    <div class="section-form p-2 shadow">
        <form action="/admin/guestbooks/reply/{{ $post->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="form-group{{ hasError('reply') }}">
                <label for="reply">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" id="reply" rows="5" name="reply" required>{{ getInput('reply', $post->reply) }}</textarea>
                <div class="invalid-feedback">{{ textError('reply') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.write') }}</button>
        </form>
    </div>
@stop
