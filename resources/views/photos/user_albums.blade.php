@extends('layout')

@section('title', __('photos.album') . ' ' . $user->getName() . ' (' . __('main.page_num', ['page' => $photos->currentPage()]) . ')')

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
                            <a href="{{ route('photos.delete', ['id' => $photo->id, 'page' => $photos->currentPage(), '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('photos.confirm_delete_photo') }}')"><i class="fa fa-times text-muted"></i></a>
                        </div>
                    @endif

                    <div class="section-title">
                        <i class="fa fa-image"></i>
                        <a href="{{ route('photos.view', ['id' => $photo->id]) }}">{{ $photo->title }}</a>
                    </div>
                </div>

                <div class="section-content">
                    @include('app/_viewer', ['model' => $photo])

                    @if ($photo->text)
                        <div class="section-message">
                            {{ bbCode($photo->text) }}
                        </div>
                    @endif

                    {{ __('main.added') }}: {{ $photo->user->getProfile() }} ({{ dateFixed($photo->created_at) }})<br>
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
