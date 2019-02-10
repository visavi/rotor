@extends('layout')

@section('title')
    {{ trans('blogs.authors') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('blogs.authors') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <i class="fa fa-pencil-alt"></i>
            <b><a href="/blogs/active/articles?user={{ $data->login }}">{{ $data->login }}</a></b> ({{ $data->cnt }} {{ trans('blogs.articles') }} / {{ $data->count_comments }} {{ trans('blogs.comments') }})<br>
        @endforeach

        {!! pagination($page) !!}

        {{ trans('blogs.total_authors') }}: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError(trans('blogs.empty_articles')) !!}
    @endif
@stop
