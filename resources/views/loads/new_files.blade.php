@extends('layout')

@section('title')
    {{ __('index.loads') }} - {{ __('loads.new_downs') }} ({{ __('main.page_num', ['page' => $downs->currentPage()]) }})
@stop

@section('header')
    <h1>{{ __('loads.new_downs') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ __('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.new_downs') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($downs->isNotEmpty())
        @foreach ($downs as $down)
            <?php $rating = $down->rated ? round($down->rating / $down->rated, 1) : 0; ?>

            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/downs/{{ $down->id }}">{{ $down->title }}</a></b> ({{ $down->count_comments }})
            </div>
            <div>
                {{ __('loads.load') }}: <a href="/loads/{{ $down->category->id }}">{{ $down->category->name }}</a><br>
                {{ __('main.rating') }}: {{ $rating }}<br>
                {{ __('main.downloads') }}: {{ $down->loads }}<br>
                {{ __('main.author') }}: {!! $down->user->getProfile() !!} ({{ dateFixed($down->created_at) }})
            </div>
        @endforeach
    @else
        {!! showError(__('loads.empty_downs')) !!}
    @endif

    {{ $downs->links('app/_paginator') }}
@stop
