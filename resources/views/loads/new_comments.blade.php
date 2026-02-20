@extends('layout')

@section('title', __('index.loads') . ' - ' . __('loads.new_comments') . ' (' . __('main.page_num', ['page' => $comments->currentPage()]) . ')')

@section('header')
    <h1>{{ __('loads.new_comments') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('loads.index') }}">{{ __('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.new_comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-comment"></i>
                    <a href="{{ route('downs.comments', ['id' => $data->relate_id, 'cid' => $data->id]) }}">{{ $data->title }}</a> <span class="badge bg-adaptive">{{ $data->count_comments }}</span>

                    @if (isAdmin())
                        <div class="float-end">
                            <a href="#" onclick="return deleteComment(this)" data-rid="{{ $data->relate_id }}" data-id="{{ $data->id }}" data-type="{{ $data->relate->getMorphClass() }}" data-bs-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                        </div>
                    @endif
                </div>

                <div class="section-content">
                    <div class="section-message">
                        {{ bbCode($data->text) }}
                    </div>

                    {{ __('main.posted') }}: {{ $data->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small><br>
                    @if (isAdmin())
                        <div class="small text-muted fst-italic mt-2">
                            {{ $data->brow }}, {{ $data->ip }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('main.empty_comments')) }}
    @endif

    {{ $comments->links() }}
@stop
