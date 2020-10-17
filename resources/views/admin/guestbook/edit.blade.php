@extends('layout')

@section('title', __('guestbook.title_edit'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/guestbook">{{ __('index.guestbook') }}</a></li>
            <li class="breadcrumb-item active">{{ __('guestbook.title_edit') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->getName() }}</b> <small>({{ dateFixed($post->created_at) }})</small><br><br>

    <div class="section-form p-2 shadow">
        <form action="/admin/guestbook/edit/{{ $post->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg', $post->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
