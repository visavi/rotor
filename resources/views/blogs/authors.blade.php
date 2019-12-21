@extends('layout')

@section('title')
    {{ __('blogs.authors') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.authors') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <i class="fa fa-pencil-alt"></i>
            <b><a href="/blogs/active/articles?user={{ $data->login }}">{{ $data->login }}</a></b> ({{ $data->cnt }} {{ __('blogs.all_articles') }} / {{ $data->count_comments }} {{ __('main.comments') }})<br>
        @endforeach

        <br>{{ __('blogs.total_authors') }}: <b>{{ $blogs->total() }}</b><br>
    @else
        {!! showError(__('blogs.empty_articles')) !!}
    @endif

    {{ $blogs->links() }}
@stop
