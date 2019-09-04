@extends('layout')

@section('title')
    {{ __('notebooks.title_edit') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item"><a href="/notebooks">{{ __('index.notebook') }}</a></li>
            <li class="breadcrumb-item active">{{ __('notebooks.title_edit') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/notebooks/edit" method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ __('main.note') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg">{{ getInput('msg', $note->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.save') }}</button>
        </form>
    </div><br>

    {{ __('notebooks.info_edit') }}<br><br>
@stop
