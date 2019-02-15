@extends('layout')

@section('title')
    {{ trans('forums.title_top_topics') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('forums.forum') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('forums.title_top_topics') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->isNotEmpty())
        @foreach ($topics as $data)
            <div class="b">
                <i class="fa {{ $data->getIcon() }} text-muted"></i>
                <b><a href="/topics/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_posts }})
            </div>
            <div>
                {!! $data->pagination() !!}
                {{ trans('main.author') }}: {{ $data->user->getName() }}<br>
                {{ trans('forums.post') }}: {{ $data->lastPost->user->getName() }} ({{ dateFixed($data->lastPost->created_at) }})
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('forums.empty_topics')) !!}
    @endif
@stop
