@extends('layout')

@section('title')
    {{ trans('news.last_comments') }}
@stop

@section('content')

    <h1>{{ trans('news.last_comments') }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ trans('news.header') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('news.last_comments') }}</li>
        </ol>
    </nav>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="b">
                <i class="fa fa-comment"></i> <b><a href="/news/comment/{{ $data->relate_id }}/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})
            </div>

            <div>
                {!! bbCode($data->text) !!}<br>
                {{ trans('news.posted_by') }}: {!! profile($data->user) !!} <small>({{ dateFixed($data->created_at) }})</small><br>

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('news.empty_comments')) !!}
    @endif
@stop
