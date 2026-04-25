@extends('layout')

@section('title', __('blogs.tag_cloud'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.tag_cloud') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($tags)
        <div style="text-align:center">
            @foreach ($tags as $tag => $count)
                @php
                    $size = $min === $max ? 14 : round(12 + (24 * log($count - $min + 1) / log($max - $min + 1)));
                @endphp
                <a href="{{ route('blogs.tag', ['tag' => urlencode($tag)]) }}"><span style="font-size:{{ $size }}px">{{ $tag }}</span></a>
            @endforeach
        </div>
    @else
        <div class="alert alert-danger">{{ __('blogs.empty_tag_cloud') }}</div>
    @endif
@stop
