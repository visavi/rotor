@extends('layout')

@section('title',__('guestbook.title_edit'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('guestbook.index') }}">{{ __('index.guestbook') }}</a></li>
            <li class="breadcrumb-item active">{{ __('guestbook.title_edit') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt text-muted"></i> <b>{{ $post->user->getName() }}</b> <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small><br><br>

    <div class="section-form mb-3 shadow">
        <form action="{{ route('guestbook.edit', ['id' => $post->id]) }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('msg') }}">
                <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" maxlength="{{ setting('guestbook_text_max') }}" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg', $post->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            @include('app/_upload_file', ['model' => $post])

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
