@extends('layout')

@section('title')
    Заметка для {{ $contact->contactor->login }}
@stop

@section('content')

    <h1>Заметка для {{ $contact->contactor->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ getUser('login') }}">{{ getUser('login') }}</a></li>
            <li class="breadcrumb-item"><a href="/contacts">Контакт-лист</a></li>
            <li class="breadcrumb-item active">Заметка</li>
        </ol>
    </nav>

    <div class="form">
        <form method="post" action="/contacts/note/{{ $contact->id }}">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Заметка:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg">{{ getInput('msg', $contact->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div>
@stop
