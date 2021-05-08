@extends('layout')

@section('title', __('ignores.note_title') . ' ' . $ignore->ignoring->login)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item"><a href="/ignores">{{ __('index.ignores') }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.note') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form method="post" action="/ignores/note/{{ $ignore->id }}">
            @csrf
            <div class="mb-3{{ hasError('msg') }}">
                <label for="msg" class="form-label">{{ __('main.note') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ __('main.note') }}">{{ getInput('msg', $ignore->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
