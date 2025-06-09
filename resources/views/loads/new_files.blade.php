@extends('layout')

@section('title', __('index.loads') . ' - ' . __('loads.new_downs') . ' (' . __('main.page_num', ['page' => $downs->currentPage()]) . ')')

@section('header')
    <h1>{{ __('loads.new_downs') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ __('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.new_downs') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($downs->isNotEmpty())
        @foreach ($downs as $down)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-file"></i>
                            <a href="{{ route('downs.view', ['id' => $down->id]) }}">{{ $down->title }}</a>
                        </div>
                    </div>

                    <div class="text-end js-rating">
                        <span class="badge bg-adaptive">{{ formatNum($down->rating) }}</span>
                    </div>
                </div>

                <div class="section-content">
                    {{ __('loads.load') }}: <a href="/loads/{{ $down->category->id }}">{{ $down->category->name }}</a><br>
                    {{ __('main.downloads') }}: {{ $down->loads }}<br>
                    {{ __('main.author') }}: {{ $down->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">{{ dateFixed($down->created_at) }}</small>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('loads.empty_downs')) }}
    @endif

    {{ $downs->links() }}
@stop
