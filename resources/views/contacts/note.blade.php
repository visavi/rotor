@extends('layout')

@section('title')
    {{ trans('contacts.note_title') }} {{ $contact->contactor->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('main.menu') }}</a></li>
            <li class="breadcrumb-item"><a href="/contacts">{{ trans('contacts.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('contacts.note') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form method="post" action="/contacts/note/{{ $contact->id }}">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('contacts.note') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ trans('contacts.note_text') }}">{{ getInput('msg', $contact->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">{{ trans('main.edit') }}</button>
        </form>
    </div>
@stop
