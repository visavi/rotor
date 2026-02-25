@extends('layout')

@section('title', sprintf('%s - %s (%s)', __('photos.album'), $user->getName(), __('main.page_num', ['page' => $photos->currentPage()])))

@section('header')
    <h1>{{ __('photos.album') }} {{ $user->getName() }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('photos.index') }}">{{ __('index.photos') }}</a></li>
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
                            <a href="{{ route('photos.edit', ['id' => $photo->id, 'page' => $photos->currentPage()]) }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                            <form action="{{ route('photos.delete', ['id' => $photo->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('photos.confirm_delete_photo') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-link p-0"><i class="fa fa-times text-muted"></i></button>
                            </form>
                        </div>
                    @endif

                    <div class="section-title">
                        <i class="fa fa-image"></i>
                        <a href="{{ route('photos.view', ['id' => $photo->id]) }}">{{ $photo->title }}</a>
                    </div>
                </div>

                <div class="section-content">
                    @include('app/_image_viewer', ['model' => $photo])

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
    @else
        {{ showError(__('photos.empty_photos')) }}
    @endif
@stop
