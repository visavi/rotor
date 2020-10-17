@extends('layout')

@section('title', __('contacts.note_title') . ' ' . $contact->contactor->login)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item"><a href="/contacts">{{ __('index.contacts') }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.note') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form p-2 shadow">
        <form method="post" action="/contacts/note/{{ $contact->id }}">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ __('main.note') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ __('main.note') }}">{{ getInput('msg', $contact->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
