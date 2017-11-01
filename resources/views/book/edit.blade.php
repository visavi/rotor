@extends('layout')

@section('title')
    {{ trans('book.title_edit') }}
@stop

@section('content')
    <h1>{{ trans('book.title_edit') }}</h1>

    <i class="fa fa-pencil text-muted"></i> <b>{!! $post->user->login !!}</b> ({{ dateFixed($post->time) }})<br><br>

    <div class="form">
        <form action="/book/edit/{{ $post->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="markItUp">{{ trans('book.message') }}:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="{{ trans('book.message_text') }}" required>{{ getInput('msg', $post->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">{{ trans('book.edit') }}</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/book">{{ trans('book.return') }}</a><br>
@stop
