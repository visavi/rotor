@extends('layout')

@section('title', __('loads.edit_comment'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('loads.index') }}">{{ __('index.loads') }}</a></li>

            @foreach ($down->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('loads.load', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item"><a href="{{ route('downs.view', ['id' => $down->id]) }}">{{ $down->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.edit_comment') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->getName() }}</b>
    <small class="section-date text-muted fst-italic">{{ dateFixed($comment->created_at) }}</small><br>

    <div class="section-form mb-3 shadow">
        <form action="{{ route('downs.edit-comment', ['id' => $comment->relate_id, 'cid' => $comment->id, 'page' => $page]) }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('msg') }}">
                <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg', $comment->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
