@extends('layout')

@section('title')
    {{ __('blogs.tag_cloud') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.tag_cloud') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div style="text-align:center">
        @foreach ($tags as $key => $val)
            <?php $fontsize = App\Models\Article::logTagSize($val, $min, $max); ?>

            <a href="/blogs/tags/{{ urlencode($key) }}"><span style="font-size:{{ $fontsize }}pt">{{ $key }}</span></a>
        @endforeach
    </div>
@stop
