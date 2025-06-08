@extends('layout')

@section('title', __('guestbook.title_edit'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.guestbook.index') }}">{{ __('index.guestbook') }}</a></li>
            <li class="breadcrumb-item active">{{ __('guestbook.title_edit') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <p>
        <i class="fa fa-pencil-alt"></i>
        @if ($post->user_id)
             <b>{{ $post->user->getName() }}</b>
        @else
            <b>{{ $post->guest_name ?: setting('guestsuser') }}</b>
        @endif
        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
    </p>

    <div class="section-form mb-3 shadow">
        <form action="{{ route('admin.guestbook.edit', ['id' => $post->id, 'page' => $page]) }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('msg') }}">
                <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg', $post->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
