@extends('layout')

@section('title', __('index.photos'))

@section('header')
    <div class="float-end">
        <a class="btn btn-success" href="{{ route('photos.create') }}">{{ __('main.add') }}</a>
        <a class="btn btn-light" href="{{ route('photos.index', ['page' => $photos->currentPage()]) }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.photos') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.photos') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($photos->isNotEmpty())
        @csrf
        @foreach ($photos as $photo)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-image"></i>
                            <a href="{{ route('photos.view', ['id' => $photo->id]) }}">{{ $photo->title }}</a>
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.photos.edit', ['id' => $photo->id, 'page' => $photos->currentPage()]) }}" data-bs-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <a href="{{ route('admin.photos.delete', ['id' => $photo->id, 'page' => $photos->currentPage()]) }}" onclick="return confirm('{{ __('photos.confirm_delete_photo') }}')" data-bs-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fas fa-times text-muted"></i></a>
                    </div>
                </div>

                <div class="section-content">
                    @include('app/_viewer', ['model' => $photo])

                    @if ($photo->text)
                        <div class="section-message">
                            {{ bbCode($photo->text) }}
                        </div>
                    @endif

                    {{ __('main.added') }}: {{ $photo->user->getProfile() }} <small class="section-date text-muted fst-italic">{{ dateFixed($photo->created_at) }}</small><br>
                    <a href="{{ route('photos.comments', ['id' => $photo->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $photo->count_comments }}</span>
                </div>
            </div>
        @endforeach

        {{ $photos->links() }}

        <div class="mb-3">
            {{ __('photos.total_photos') }}: <b>{{ $photos->total() }}</b>
        </div>

        @if (isAdmin('boss'))
            <i class="fa fa-sync"></i> <a href="{{ route('admin.photos.restatement', ['_token' => csrf_token()]) }}">{{ __('main.recount') }}</a><br>
        @endif
    @else
        {{ showError(__('photos.empty_photos')) }}
    @endif
@stop
