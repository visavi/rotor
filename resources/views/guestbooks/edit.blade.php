@extends('layout')

@section('title')
    {{ trans('guestbooks.title_edit') }}
@stop

@section('content')
    <h1>{{ trans('guestbooks.title_edit') }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/">{{ trans('guestbooks.header') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('guestbooks.title_edit') }}</li>
        </ol>
    </nav>

    <i class="fa fa-pencil-alt text-muted"></i> <b>{!! $post->user->login !!}</b> ({{ dateFixed($post->created_at) }})<br><br>

    <div class="form">
        <form action="/guestbooks/edit/{{ $post->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('guestbooks.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5"  maxlength="{{ setting('guesttextlength') }}" name="msg" placeholder="{{ trans('guestbooks.message_text') }}" required>{{ getInput('msg', $post->text) }}</textarea>
                <span class="js-textarea-counter"></span>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">{{ trans('guestbooks.edit') }}</button>
        </form>
    </div><br>
@stop
