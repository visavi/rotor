@extends('layout')

@section('title', __('index.loads'))

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/downs/create">{{ __('main.add') }}</a>
        </div><br>
    @endif

    <h1>{{ __('index.loads') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.loads') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser())
        {{ __('main.my') }}: <a href="/downs/active/files">{{ __('loads.downs') }}</a>, <a href="/downs/active/comments">{{ __('main.comments') }}</a> /
    @endif

    {{ __('main.new') }}: <a href="/downs">{{ __('loads.downs') }}</a>, <a href="/downs/comments">{{ __('main.comments') }}</a>
    <hr>

    @foreach ($categories as $category)
        <div class="b">
            <i class="fa fa-folder-open"></i>
            <b><a href="/loads/{{ $category->id }}">{{ $category->name }}</a></b>

            @if ($category->new)
                ({{ $category->count_downs + $category->children->sum('count_downs') }}/<span style="color:#ff0000">+{{ $category->new->count_downs }}</span>)<br>
            @else
                ({{ $category->count_downs + $category->children->sum('count_downs') }})<br>
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
    <a href="/loads/top">{{ __('loads.top_downs') }}</a> /
    <a href="/loads/search">{{ __('main.search') }}</a> /
    <a href="/loads/rss">{{ __('main.rss') }}</a><br>
@stop
