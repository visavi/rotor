@extends('layout')

@section('title')
    {{ trans('main.last_comments') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ trans('index.news') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('main.last_comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="b">
                <i class="fa fa-comment"></i> <b><a href="/news/comment/{{ $data->relate_id }}/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})
            </div>

            <div>
                {!! bbCode($data->text) !!}<br>
                {{ trans('main.posted') }}: {!! $data->user->getProfile() !!} <small>({{ dateFixed($data->created_at) }})</small><br>

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('main.empty_comments')) !!}
    @endif
@stop
