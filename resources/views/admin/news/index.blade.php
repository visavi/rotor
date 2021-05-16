@extends('layout')

@section('title', __('index.news'))

@section('header')
    <div class="float-end">
        <a class="btn btn-success" href="/admin/news/create">{{ __('main.create') }}</a>
        <a class="btn btn-light" href="/news"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.news') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.news') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($news->isNotEmpty())
        @foreach ($news as $data)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <i class="fa {{ $data->getIcon() }} text-muted"></i>
                        <a class="section-title" href="/news/{{ $data->id }}">{{ $data->title }}</a>
                        <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }})</small>
                    </div>

                    <div class="text-end">
                        @if ($data->top)
                            <span class="text-danger">{{ __('news.on_homepage') }}</span><br>
                        @endif
                        <a href="/admin/news/edit/{{ $data->id }}?page={{ $news->currentPage() }}" data-bs-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <a href="/admin/news/delete/{{ $data->id }}?token={{ $_SESSION['token'] }}" data-bs-toggle="tooltip" title="{{ __('main.delete') }}" onclick="return confirm('{{ __('news.confirm_delete') }}')"><i class="fas fa-times text-muted"></i></a>
                    </div>
                </div>

                <div class="section-content">
                    <div class="section-message row mb-3">
                        @if ($data->image)
                            <div class="col-sm-3 mb-3">
                                <a href="{{ $data->image }}" class="gallery">{{ resizeImage($data->image, ['class' => 'img-thumbnail img-fluid', 'alt' => $data->title]) }}</a>
                            </div>
                        @endif

                        <div class="col">
                            {{ $data->shortText() }}
                        </div>
                    </div>

                    <div>
                        {{ __('main.added') }}: {{ $data->user->getProfile() }}<br>
                        <a href="/news/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                        <a href="/news/end/{{ $data->id }}">&raquo;</a>
                    </div>
                </div>
            </div>
        @endforeach

        {{ __('news.total_news') }}: <b>{{ $news->count() }}</b><br><br>
    @else
        {{ showError(__('news.empty_news')) }}
    @endif

    {{ $news->links() }}

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/news/restatement?token={{ $_SESSION['token'] }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
