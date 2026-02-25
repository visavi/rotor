@extends('layout')

@section('title', __('index.news'))

@section('header')
    <div class="float-end">
        <a class="btn btn-success" href="{{ route('admin.news.create') }}">{{ __('main.create') }}</a>
        <a class="btn btn-light" href="{{ route('news.index') }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.news') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.news') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($news->isNotEmpty())
        @foreach ($news as $data)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-start">
                    <div class="flex-grow-1">
                        <i class="fa {{ $data->getIcon() }} text-muted"></i>
                        <a class="section-title" href="{{ route('news.view', ['id' => $data->id]) }}">{{ $data->title }}</a>
                        <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small>
                    </div>

                    <div class="text-end">
                        @if ($data->top)
                            <span class="text-danger">{{ __('news.on_homepage') }}</span><br>
                        @endif
                        <a href="{{ route('admin.news.edit', ['id' => $data->id, 'page' => $news->currentPage()]) }}" data-bs-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <form action="{{ route('admin.news.delete', ['id' => $data->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('news.confirm_delete') }}')">
                            @csrf
                            <button class="btn btn-link p-0" data-bs-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fas fa-times text-muted"></i></button>
                        </form>
                    </div>
                </div>

                <div class="section-content short-view">
                    @if ($data->getImages()->isNotEmpty())
                        @include('app/_image_viewer', ['model' => $data, 'files' => $data->getImages()])
                    @endif

                    <div class="section-message">
                        {{ $data->getText() }}
                    </div>

                    @if ($data->getFiles()->isNotEmpty())
                        @foreach ($data->getFiles() as $file)
                            <div class="media-file">
                                @if ($file->isVideo())
                                    <div>
                                        <video src="{{ $file->path }}" class="img-fluid rounded" preload="metadata" controls playsinline></video>
                                    </div>
                                @endif

                                @if ($file->isAudio())
                                    <div>
                                        <audio src="{{ $file->path }}" class="img-fluid rounded" preload="metadata" controls></audio>
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

        {{ __('news.total_news') }}: <b>{{ $news->count() }}</b><br><br>
    @else
        {{ showError(__('news.empty_news')) }}
    @endif

    {{ $news->links() }}

    @if (isAdmin('boss'))
        <form action="{{ route('admin.news.restatement') }}" method="post">
            @csrf
            <button class="btn btn-primary">
                <i class="fa fa-sync"></i> {{ __('main.recount') }}
            </button>
        </form>
    @endif
@stop
