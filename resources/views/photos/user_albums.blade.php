@extends('layout')

@section('title', __('photos.album') . ' ' . $user->getName() . ' (' . __('main.page_num', ['page' => $photos->currentPage()]) . ')')

@section('header')
    <h1>{{ __('photos.album') }} {{ $user->getName() }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/photos">{{ __('index.photos') }}</a></li>
            <li class="breadcrumb-item active">{{ __('photos.album') }} {{ $user->getName() }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($photos->isNotEmpty())
        @foreach ($photos as $photo)
            <div class="section mb-3 shadow">
                <div class="section-header">
                    @if ($moder)
                        <div class="float-end">
                            <a href="/photos/edit/{{ $photo->id }}?page={{ $photos->currentPage() }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                            <a href="/photos/delete/{{ $photo->id }}?page={{ $photos->currentPage() }}&amp;_token={{ csrf_token() }}" onclick="return confirm('{{ __('photos.confirm_delete_photo') }}')"><i class="fa fa-times text-muted"></i></a>
                        </div>
                    @endif

                    <div class="section-title">
                        <i class="fa fa-image"></i>
                        <a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a>
                    </div>
                </div>

                <div class="section-content">
                    @include('app/_carousel', ['model' => $photo])

                    @if ($photo->text)
                       {{ bbCode($photo->text) }}<br>
                    @endif

                    {{ __('main.added') }}: {{ $photo->user->getProfile() }} ({{ dateFixed($photo->created_at) }})<br>
                    <a href="/photos/comments/{{ $photo->id }}">{{ __('main.comments') }}</a> ({{ $photo->count_comments }})
                </div>
            </div>
        @endforeach

        {{ $photos->links() }}

        <div class="mb-3">
            {{ __('photos.total_photos') }}: <b>{{ $photos->total() }}</b>
        </div>
    @else
        {{ showError(__('photos.empty_photos')) }}
    @endif
@stop
