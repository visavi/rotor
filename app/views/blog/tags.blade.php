@extends('layout')

@section('title')
    Облако тегов - @parent
@stop

@section('content')
    <h1>Облако тегов</h1>

    <div style="text-align:center">
        @foreach ($tags as $key => $val)

            <?php $fontsize = ($min != $max) ? round((($val - $min) / ($max - $min)) * 110 + 100) : 100; ?>

            <a href="/blog/tags/{{ urlencode($key) }}"><span style="font-size:{{ $fontsize }}%">{{ $key }}</span></a>
        @endforeach
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/blog">К блогам</a><br>
@stop
