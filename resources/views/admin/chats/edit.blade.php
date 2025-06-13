@extends('layout')

@section('title', __('admin.chat.edit_message'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/chats">{{ __('index.admin_chat') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.chat.edit_message') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt text-muted"></i> <b>{{ $post->user->getName() }}</b> <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small><br><br>

    <div class="section-form mb-3 shadow">
        <form action="/admin/chats/edit/{{ $post->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('msg') }}">
                <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg', $post->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
