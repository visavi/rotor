@extends('layout')

@section('title', __('index.news') . ' (' . __('main.page_num', ['page' => $news->currentPage()]) . ')')

@section('header')
    @if (isAdmin('moder'))
        <div class="float-right">
            <a class="btn btn-light" href="/admin/news"><i class="fas fa-wrench"></i></a>
        </div>
    @endif

    <h1>{{ __('index.news') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
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
                        <i class="fa fa-file-alt fa-lg text-muted"></i>
                        <a class="section-title" href="/news/{{ $data->id }}">{{ $data->title }}</a>

                        <small class="section-date text-muted font-italic">
                            {{ dateFixed($data->created_at) }}
                        </small>
                    </div>

                    <div class="js-rating">
                        @if (getUser() && getUser('id') !== $data->user_id)
                            <a class="post-rating-down{{ $data->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
                        @endif
                        <b>{{ formatNum($data->rating) }}</b>
                        @if (getUser() && getUser('id') !== $data->user_id)
                            <a class="post-rating-up{{ $data->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
                        @endif
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
    @else
        {{ showError(__('news.empty_news')) }}
    @endif

    {{ $news->links() }}

    <i class="fa fa-rss"></i> <a href="/news/rss">{{ __('main.rss') }}</a><br>
    <i class="fa fa-comment"></i> <a href="/news/allcomments">{{ __('main.last_comments') }}</a><br>
@stop
