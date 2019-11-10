@extends('layout')

@section('title')
    {{ __('main.search_request') }} {{ $find }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ __('index.loads') }}</a></li>
            <li class="breadcrumb-item"><a href="/loads/search">{{ __('loads.search') }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.search_request') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('loads.found_title') }}: <b>{{ $downs->total() }}</b><br><br>

    @foreach ($downs as $data)
        <?php $rating = $data->rated ? round($data->rating / $data->rated, 1) : 0; ?>

        <div class="b">
            <i class="fa fa-file"></i>
            <b><a href="/downs/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})
        </div>

        <div>
            {{ __('loads.load') }}: <a href="/loads/{{ $data->category->id }}">{{ $data->category->name }}</a><br>
            {{ __('main.rating') }}: {{ $rating }}<br>
            {{ __('main.downloads') }}: {{ $data->loads }}<br>
            {{ __('main.author') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})
        </div>
    @endforeach

    {{ $downs->links('app/_paginator') }}
@stop
