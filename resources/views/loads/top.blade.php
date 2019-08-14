@extends('layout')

@section('title')
    {{ trans('loads.top_downs') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ trans('loads.top_downs') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ trans('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('loads.top_downs') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('main.sort') }}:
    <?php $active = ($order === 'loads') ? 'success' : 'light'; ?>
    <a href="/loads/top?sort=loads" class="badge badge-{{ $active }}">{{ trans('main.downloads') }}</a>

    <?php $active = ($order === 'rated') ? 'success' : 'light'; ?>
    <a href="/loads/top?sort=rated" class="badge badge-{{ $active }}">{{ trans('main.rating') }}</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/loads/top?sort=comments" class="badge badge-{{ $active }}">{{ trans('main.comments') }}</a>
    <hr>

    @if ($downs->isNotEmpty())

        @foreach ($downs as $data)
            <?php $rating = $data->rated ? round($data->rating / $data->rated, 1) : 0; ?>

            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/downs/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})
            </div>

            <div>
                {{ trans('loads.load') }}: <a href="/loads/{{ $data->category->id }}">{{ $data->category->name }}</a><br>
                {{ trans('main.rating') }}: {{ $rating }}<br>
                {{ trans('main.downloads') }}: {{ $data->loads }}<br>
                <a href="/downs/comments/{{ $data->id }}">{{ trans('main.comments') }}</a> ({{ $data->count_comments }})
                <a href="/downs/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('loads.empty_downs')) !!}
    @endif
@stop
