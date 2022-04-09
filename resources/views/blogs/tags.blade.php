@extends('layout')

@section('title', __('blogs.tag_cloud'))

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
    @if ($tags)
        <div style="text-align:center">
            @foreach ($tags as $key => $val)
                <?php $fontsize = App\Models\Article::logTagSize($val, $min, $max); ?>

                <a href="/blogs/tags/{{ urlencode($key) }}"><span style="font-size:{{ $fontsize }}pt">{{ $key }}</span></a>
            @endforeach
        </div>
    @else
        <div class="alert alert-danger">{{ __('blogs.empty_tag_cloud') }}</div>
    @endif
@stop
