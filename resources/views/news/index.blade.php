@extends('layout')

@section('title', __('index.news') . ' (' . __('main.page_num', ['page' => $news->currentPage()]) . ')')

@section('header')
    @if (isAdmin('moder'))
        <div class="float-end">
            <a class="btn btn-light" href="{{ route('admin.news.index') }}"><i class="fas fa-wrench"></i></a>
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
                        <a class="section-title" href="{{ route('news.view', ['id' => $data->id]) }}">{{ $data->title }}</a>

                        <small class="section-date text-muted fst-italic">
                            {{ dateFixed($data->created_at) }}
                        </small>
                    </div>

                    <div class="section-action js-rating">
                        @if (getUser() && getUser('id') !== $data->user_id)
                            <a class="post-rating-down{{ $data->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fas fa-arrow-down"></i></a>
                        @endif
                        <b>{{ formatNum($data->rating) }}</b>
                        @if (getUser() && getUser('id') !== $data->user_id)
                            <a class="post-rating-up{{ $data->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fas fa-arrow-up"></i></a>
                        @endif
                    </div>
                </div>

                <div class="section-content">
                    <div class="section-message">
                        {{ $data->shortText() }}
                    </div>

                    @if ($data->getImages()->isNotEmpty())
                        @include('app/_viewer', ['model' => $data, 'files' => $data->getImages()])
                    @endif

                    @if ($data->getFiles()->isNotEmpty())
                        @foreach ($data->getFiles() as $file)
                            <div class="media-file">
                                @if ($file->isVideo())
                                    <div>
                                        <video src="{{ $file->path }}" style="max-width:100%;" preload="metadata" controls playsinline></video>
                                    </div>
                                @endif

                                @if ($file->isAudio())
                                    <div>
                                        <audio src="{{ $file->path }}" style="max-width:100%;" preload="metadata" controls></audio>
                                    </div>
                                @endif

                                {{ icons($file->extension) }}
                                <a href="{{ $file->path }}">{{ $file->name }}</a> ({{ formatSize($file->size) }})
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="section-body">
                    <span class="avatar-micro">{{ $data->user->getAvatarImage() }}</span> {{ $data->user->getProfile() }}
                </div>

                <i class="fa-regular fa-comment"></i> <a href="{{ route('news.comments', ['id' => $data->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $data->count_comments }}</span>
            </div>
        @endforeach
    @else
        {{ showError(__('news.empty_news')) }}
    @endif

    {{ $news->links() }}

    <i class="fa fa-rss"></i> <a href="{{ route('news.rss') }}">{{ __('main.rss') }}</a><br>
    <i class="fa fa-comment"></i> <a href="{{ route('news.all-comments') }}">{{ __('main.last_comments') }}</a><br>
@stop
