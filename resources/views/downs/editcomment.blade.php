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
    @include('app/_comment_edit_form', [
        'action' => route('downs.edit-comment', ['id' => $comment->relate_id, 'cid' => $comment->id, 'page' => $page]),
    ])
@stop
