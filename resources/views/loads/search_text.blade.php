@extends('layout')

@section('title')
    {{ trans('main.search_request') }} {{ $find }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ trans('loads.title') }}</a></li>
            <li class="breadcrumb-item"><a href="/loads/search">{{ trans('loads.search') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('main.search_request') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('loads.found_text') }}: <b>{{ $page->total }}</b><br><br>

    @foreach ($downs as $data)
        <?php $rating = $data->rated ? round($data->rating / $data->rated, 1) : 0; ?>

        <div class="b">
            <i class="fa fa-file"></i>
            <b><a href="/downs/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})
        </div>

        <div>
            {!! $data->shortText() !!}<br>

            {{ trans('loads.load') }}: <a href="/loads/{{ $data->category->id }}">{{ $data->category->name }}</a><br>
            {{ trans('main.rating') }}: {{ $rating }}<br>
            {{ trans('main.author') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
