@extends('layout')

@section('title')
    {{ trans('loads.title') }}
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/downs/create">{{ trans('loads.create_down') }}</a>
        </div><br>
    @endif

    <h1>{{ trans('loads.title') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('loads.title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser())
        {{ trans('main.my') }}: <a href="/downs/active/files">{{ trans('loads.downs') }}</a>, <a href="/downs/active/comments">{{ trans('loads.comments') }}</a> /
    @endif

    {{ trans('main.new') }}: <a href="/downs">{{ trans('loads.downs') }}</a>, <a href="/downs/comments">{{ trans('loads.comments') }}</a>
    <hr>

    @foreach ($categories as $category)
        <div class="b">
            <i class="fa fa-folder-open"></i>
            <b><a href="/loads/{{ $category->id }}">{{ $category->name }}</a></b>
            @if ($category->new)
                ({{ $category->count_downs }}/<span style="color:#ff0000">+{{ $category->new->count_downs }}</span>)<br>
            @else
                ({{ $category->count_downs }})<br>
            @endif
        </div>

        <div>
            @if ($category->children->isNotEmpty())
                @foreach ($category->children as $child)
                    <i class="fa fa-angle-right"></i> <b><a href="/loads/{{ $child->id }}">{{ $child->name }}</a></b>
                    @if ($child->new)
                        ({{ $child->count_downs }}/<span style="color:#ff0000">+{{ $child->new->count_downs }}</span>)<br>
                    @else
                        ({{ $child->count_downs }})<br>
                    @endif
                @endforeach
            @endif
        </div>
    @endforeach

    <br>
    <a href="/loads/top">{{ trans('loads.downs') }}</a> /
    <a href="/loads/search">{{ trans('main.search') }}</a> /
    <a href="/loads/rss">{{ trans('main.rss') }}</a><br>
@stop
