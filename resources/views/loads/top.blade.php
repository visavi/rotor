@extends('layout')

@section('title', __('loads.top_downs') . ' (' . __('main.page_num', ['page' => $downs->currentPage()]) . ')')

@section('header')
    <h1>{{ __('loads.top_downs') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('loads.index') }}">{{ __('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.top_downs') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($downs->isNotEmpty())
        <div class="sort-links border-bottom pb-3 mb-3">
            {{ __('main.sort') }}:
            @foreach ($sorting as $key => $option)
                <a href="{{ route('loads.top', ['sort' => $key, 'order' => $option['inverse'] ?? 'desc']) }}" class="badge bg-{{ $option['badge'] ?? 'adaptive' }}">
                    {{ $option['label'] }}{{ $option['icon'] ?? '' }}
                </a>
            @endforeach
        </div>

        @foreach ($downs as $data)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-file"></i>
                            <a href="{{ route('downs.view', ['id' => $data->id]) }}">{{ $data->title }}</a>
                        </div>
                    </div>

                    <div class="text-end js-rating">
                        <span class="badge bg-adaptive">{{ formatNum($data->rating) }}</span>
                    </div>
                </div>

                <div class="section-content">
                    {{ __('loads.load') }}: <a href="{{ route('loads.load', ['id' => $data->category->id]) }}">{{ $data->category->name }}</a><br>
                    {{ __('main.downloads') }}: {{ $data->loads }}<br>
                    <a href="{{ route('downs.comments', ['id' => $data->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $data->count_comments }}</span>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('loads.empty_downs')) }}
    @endif

    {{ $downs->links() }}
@stop
