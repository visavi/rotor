@extends('layout')

@section('title')
    Облако тегов
@stop

@section('content')
    <h1>Облако тегов</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">Блоги</a></li>
            <li class="breadcrumb-item active">Облако тегов</li>
        </ol>
    </nav>

    <div style="text-align:center">
        @foreach ($tags as $key => $val)

            <?php
            $fontsize = App\Models\Blog::logTagSize($val, $min, $max);
            ?>

            <a href="/blogs/tags/{{ urlencode($key) }}"><span style="font-size:{{ $fontsize }}pt">{{ $key }}</span></a>
        @endforeach
    </div>
@stop
