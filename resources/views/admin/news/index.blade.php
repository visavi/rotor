@extends('layout')

@section('title', __('index.news'))

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/admin/news/create">{{ __('main.create') }}</a>
    </div>

    <h1>{{ __('index.news') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.news') }}</li>
            <li class="breadcrumb-item"><a href="/news">{{ __('main.review') }}</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($news->isNotEmpty())
        @foreach ($news as $data)
            <div class="b">
                <div class="float-right">
                    @if ($data->top)
                        <div class="right"><span style="color:#ff0000">{{ __('news.on_homepage') }}</span></div>
                    @endif
                </div>

                <i class="fa {{ $data->getIcon() }} text-muted"></i>

                <b><a href="/news/{{ $data->id }}">{{ $data->title }}</a></b><small> ({{ dateFixed($data->created_at) }})</small><br>

                <div class="float-right">
                    <a href="/admin/news/edit/{{ $data->id }}?page={{ $news->currentPage() }}" data-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/news/delete/{{ $data->id }}?token={{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}" onclick="return confirm('{{ __('news.confirm_delete') }}')"><i class="fas fa-times text-muted"></i></a>
                </div>

            </div>

            @if ($data->image)
                <div class="img">
                    <a href="{{ $data->image }}" class="gallery">{!! resizeImage($data->image, ['width' => 100, 'alt' => $data->title]) !!}</a>
                </div>
            @endif

            <div class="clearfix">{!! $data->shortText() !!}</div>

            <div>{{ __('main.added') }}: {!! $data->user->getProfile() !!}<br>
                <a href="/news/comments/{{  $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                <a href="/news/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {{ __('news.total_news') }}: <b>{{ $news->count() }}</b><br><br>
    @else
        {!! showError(__('news.empty_news')) !!}
    @endif

    {{ $news->links() }}

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/news/restatement?token={{ $_SESSION['token'] }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
