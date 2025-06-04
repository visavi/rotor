@extends('layout')

@section('title', $category->name . ' (' . __('main.page_num', ['page' => $downs->currentPage()])  . ')')

@section('header')
    <div class="float-end">
        @if (getUser())
            @if (! $category->closed)
                    <a class="btn btn-success" href="/downs/create?category={{ $category->id }}">{{ __('main.add') }}</a>

            @endif

            @if (isAdmin())
                <a class="btn btn-light" href="/admin/loads/{{ $category->id }}?page={{ $downs->currentPage() }}"><i class="fas fa-wrench"></i></a>
            @endif
        @endif
    </div>

    <h1>{{ $category->name }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ __('index.loads') }}</a></li>

            @foreach ($category->getParents() as $parent)
                @if ($loop->last)
                    <li class="breadcrumb-item active">{{ $parent->name }}</li>
                @else
                    <li class="breadcrumb-item"><a href="/loads/{{ $parent->id }}">{{ $parent->name }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
@stop

@section('content')
    <div class="sort-links border-bottom pb-3 mb-3">
        {{ __('main.sort') }}:

        @foreach ($sortOptions as $key => $option)
            @php
                $isActive = ($sort === $key);
                $badgeClass = $isActive ? 'success' : 'adaptive';
                $oppositeOrder = ($order === 'asc') ? 'desc' : 'asc';
            @endphp
            <a href="{{ route('loads.load', ['id' => $category->id, 'sort' => $key, 'order' => $isActive ? $oppositeOrder : 'desc']) }}" class="badge bg-{{ $badgeClass }}">
                {{ $option['label'] }}
                @if ($isActive)
                    <span>{{ $order === 'asc' ? '↑' : '↓' }}</span>
                @endif
            </a>
        @endforeach
    </div>

    @if ($downs->onFirstPage() && $category->children->isNotEmpty())
        @php $category->children->load(['children', 'lastDown.user']); @endphp
        @foreach ($category->children as $child)
            <div class="section mb-3 shadow border-start border-info border-5">
                <div class="section-title">
                    <i class="fa fa-folder-open"></i>
                    <a href="/loads/{{ $child->id }}">{{ $child->name }}</a> ({{ $child->count_downs + $child->children->sum('count_downs') }})
                </div>

                <div class="section-body border-top">
                    @if ($child->lastDown)
                        {{ __('loads.down') }}: <a href="/downs/{{ $child->lastDown->id }}">{{ $child->lastDown->title }}</a>

                        @if ($child->lastDown->isNew())
                            <span class="badge text-bg-success">NEW</span>
                        @endif
                        <br>
                        {{ __('main.author') }}: {{ $child->lastDown->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">
                            {{ dateFixed($child->lastDown->created_at) }}
                        </small>
                    @else
                        {{ __('loads.empty_downs') }}
                    @endif
                </div>
            </div>
        @endforeach
        <hr>
    @endif

    @if ($downs->isNotEmpty())
        @foreach ($downs as $data)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-file"></i>
                            <a href="/downs/{{ $data->id }}">{{ $data->title }}</a>
                            @if ($data->isNew())
                                <span class="badge text-bg-success">NEW</span>
                            @endif
                        </div>
                    </div>

                    <div class="text-end js-rating">
                        <b>{{ formatNum($data->rating) }}</b>
                    </div>
                </div>

                <div class="section-content">
                    <div class="mb-2">
                        {{ __('main.downloads') }}: {{ $data->loads }}<br>
                        {{ __('main.author') }}: {{ $data->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">
                            {{ dateFixed($data->created_at) }}
                        </small>
                    </div>

                    <a href="/downs/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                    <a href="/downs/end/{{ $data->id }}">&raquo;</a>
                </div>
            </div>
        @endforeach
    @elseif (! $category->closed)
        {{ showError(__('loads.empty_downs')) }}
    @endif

    {{ $downs->links() }}
@stop
