@extends('layout')

@section('title', __('index.photos'))

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/photos/create">{{ __('main.add') }}</a><br>
        </div>
    @endif

    <h1>{{ __('index.photos') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.photos') }}</li>
            <li class="breadcrumb-item"><a href="/photos?page={{ $photos->currentPage() }}">{{ __('main.review') }}</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($photos->isNotEmpty())
        @csrf
        @foreach ($photos as $photo)
            <div class="b">
                <i class="fa fa-image"></i>
                <b><a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a></b>

                <div class="float-right">
                    <a href="/admin/photos/edit/{{ $photo->id }}?page={{ $photos->currentPage() }}" data-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/photos/delete/{{ $photo->id }}?page={{ $photos->currentPage() }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('photos.confirm_delete_photo') }}')" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fas fa-times text-muted"></i></a>
                </div>
            </div>

            <div>
                @include('app/_carousel', ['model' => $photo, 'path' => '/photos'])

                @if ($photo->text)
                    {!! bbCode($photo->text) !!}<br>
                @endif

                    {{ __('main.added') }}: {!! $photo->user->getProfile() !!} ({{ dateFixed($photo->created_at) }})<br>
                <a href="/photos/comments/{{ $photo->id }}">{{ __('main.comments') }}</a> ({{ $photo->count_comments }})
                <a href="/photos/end/{{ $photo->id }}">&raquo;</a>
            </div>
        @endforeach

        {{ __('photos.total_photos') }}: <b>{{ $photos->total() }}</b><br><br>

        @if (isAdmin('boss'))
            <i class="fa fa-sync"></i> <a href="/admin/photos/restatement?token={{ $_SESSION['token'] }}">{{ __('main.recount') }}</a><br>
        @endif
    @else
        {!! showError(__('photos.empty_photos')) !!}
    @endif

    {{ $photos->links() }}
@stop
