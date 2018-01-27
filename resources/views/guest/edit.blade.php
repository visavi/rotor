@extends('layout')

@section('title')
    {{ trans('guest.title_edit') }}
@stop

@section('content')
    <h1>{{ trans('guest.title_edit') }}</h1>

    <i class="fa fa-pencil-alt text-muted"></i> <b>{!! $post->user->login !!}</b> ({{ dateFixed($post->created_at) }})<br><br>

    <div class="form">
        <form action="/book/edit/{{ $post->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('guest.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ trans('guest.message_text') }}" required>{{ getInput('msg', $post->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">{{ trans('guest.edit') }}</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/book">{{ trans('guest.return') }}</a><br>
@stop
