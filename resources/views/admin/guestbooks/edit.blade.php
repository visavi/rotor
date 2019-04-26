@extends('layout')

@section('title')
    {{ trans('guestbooks.title_edit') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/guestbooks">{{ trans('guestbooks.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('guestbooks.title_edit') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br><br>

    <div class="form">
        <form action="/admin/guestbooks/edit/{{ $post->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('guestbooks.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ trans('guestbooks.message') }}" required>{{ getInput('msg', $post->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">{{ trans('guestbooks.edit') }}</button>
        </form>
    </div>
@stop
