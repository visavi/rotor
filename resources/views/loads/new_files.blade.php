@extends('layout')

@section('title', __('index.loads') . ' - ' . __('loads.new_downs') . ' (' . __('main.page_num', ['page' => $downs->currentPage()]) . ')')

@section('header')
    <h1>{{ __('loads.new_downs') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('loads.index') }}">{{ __('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.new_downs') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($downs->isNotEmpty())
        <div class="sort-links border-bottom pb-3 mb-3">
            {{ __('main.sort') }}:
            @foreach ($sorting as $key => $option)
                <a href="{{ route('downs.new-files', ['sort' => $key, 'order' => $option['inverse'] ?? 'desc']) }}" class="badge bg-{{ $option['badge'] ?? 'adaptive' }}">
                    {{ $option['label'] }}{{ $option['icon'] ?? '' }}
                </a>
            @endforeach
        </div>

        @foreach ($downs as $down)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-file"></i>
                            <a href="{{ route('downs.view', ['id' => $down->id]) }}">{{ $down->title }}</a>
                        </div>
                    </div>

                    <div class="text-end section-action js-rating">
                        <span class="badge bg-adaptive">{{ formatNum($down->rating) }}</span>
                    </div>
                </div>

                <div class="section-content mb-2">
                    {{ __('loads.load') }}: <a href="{{ route('loads.load', ['id' => $down->category->id]) }}">{{ $down->category->name }}</a><br>
                    {{ __('main.downloads') }}: {{ $down->loads }}<br>

                    <div class="section-body">
                        <span class="avatar-micro">{{ $down->user->getAvatarImage() }}</span> {{ $down->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">{{  dateFixed($down->created_at) }}</small><br>
                    </div>
                </div>

                <i class="fa-regular fa-comment"></i> <a href="{{ route('downs.comments', ['id' => $down->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $down->count_comments }}</span>
            </div>
        @endforeach
    @else
        {{ showError(__('loads.empty_downs')) }}
    @endif

    {{ $downs->links() }}
@stop
