@extends('layout')

@section('title', __('index.photos'))

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/photos/create">{{ __('main.add') }}</a>
        <a class="btn btn-light" href="/photos?page={{ $photos->currentPage() }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.photos') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
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
                            <a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a>
                        </div>
                    </div>

                    <div class="text-right">
                        <a href="/admin/photos/edit/{{ $photo->id }}?page={{ $photos->currentPage() }}" data-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <a href="/admin/photos/delete/{{ $photo->id }}?page={{ $photos->currentPage() }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('photos.confirm_delete_photo') }}')" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fas fa-times text-muted"></i></a>
                    </div>
                </div>

                <div class="section-content">
                    @include('app/_carousel', ['model' => $photo, 'path' => '/photos'])

                    @if ($photo->text)
                        {!! bbCode($photo->text) !!}<br>
                    @endif

                    {{ __('main.added') }}: {!! $photo->user->getProfile() !!} ({{ dateFixed($photo->created_at) }})<br>
                    <a href="/photos/comments/{{ $photo->id }}">{{ __('main.comments') }}</a> ({{ $photo->count_comments }})
                    <a href="/photos/end/{{ $photo->id }}">&raquo;</a>
                </div>
            </div>
        @endforeach

        {{ $photos->links() }}

        {{ __('photos.total_photos') }}: <b>{{ $photos->total() }}</b><br>

        @if (isAdmin('boss'))
            <i class="fa fa-sync"></i> <a href="/admin/photos/restatement?token={{ $_SESSION['token'] }}">{{ __('main.recount') }}</a><br>
        @endif
    @else
        {!! showError(__('photos.empty_photos')) !!}
    @endif
@stop
