@extends('layout')

@section('title', sprintf('%s - %s (%s)', $down->title, __('main.comments'), __('main.page_num', ['page' => $comments->currentPage()])))

@section('header')
    <h1>{{ $down->title }} - {{ __('main.comments') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('loads.index') }}">{{ __('index.loads') }}</a></li>

            @foreach ($down->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('loads.load', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item"><a href="{{ route('downs.view', ['id' => $down->id]) }}">{{ $down->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fas fa-rss"></i> <a href="{{ route('downs.rss', ['id' => $down->id]) }}">{{ __('main.rss') }}</a>
    <hr>

    @foreach ($comments as $comment)
        @include('app/_comment_item', ['editRoute' => 'downs.edit-comment', 'parentId' => $down->id])
    @endforeach

    {{ $comments->links() }}

    @include('app/_comment_form', ['action' => route('downs.comments', ['id' => $down->id])])
@stop
