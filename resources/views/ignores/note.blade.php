@extends('layout')

@section('title')
    {{ trans('ignores.note_title') }} {{ $ignore->ignoring->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('common.menu') }}</a></li>
            <li class="breadcrumb-item"><a href="/ignores">{{ trans('ignores.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('ignores.note') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form method="post" action="/ignores/note/{{ $ignore->id }}">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('ignores.note') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ trans('ignores.note_text') }}">{{ getInput('msg', $ignore->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">{{ trans('ignores.edit') }}</button>
        </form>
    </div>
@stop
