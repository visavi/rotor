@extends('layout')

@section('title', sprintf('%s (%s)', $category->name, __('main.page_num', ['page' => $downs->currentPage()])))

@section('header')
    <div class="float-end">
        @if (getUser())
            @if (! $category->closed)
                    <a class="btn btn-success" href="{{ route('downs.create', ['category' => $category->id]) }}">{{ __('main.add') }}</a>
            @endif

            @if (isAdmin())
                <a class="btn btn-light" href="{{ route('admin.loads.load', ['id' => $category->id, 'page' => $downs->currentPage()]) }}"><i class="fas fa-wrench"></i></a>
            @endif
        @endif
    </div>

    <h1>{{ $category->name }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('loads.index') }}">{{ __('index.loads') }}</a></li>

            @foreach ($category->getParents() as $parent)
                @if ($loop->last)
                    <li class="breadcrumb-item active">{{ $parent->name }}</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ route('loads.load', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
@stop

@section('content')
    <div class="sort-links border-bottom pb-3 mb-3">
        {{ __('main.sort') }}:
        @foreach ($sorting as $key => $option)
            <a href="{{ route('loads.load', ['id' => $category->id, 'sort' => $key, 'order' => $option['inverse'] ?? 'desc']) }}" class="badge bg-{{ $option['badge'] ?? 'adaptive' }}">
                {{ $option['label'] }}{{ $option['icon'] ?? '' }}
            </a>
        @endforeach
    </div>

    @if ($downs->onFirstPage() && $category->children->isNotEmpty())
        @php $category->children->load(['children', 'lastDown.user']); @endphp
        @foreach ($category->children as $child)
            <div class="section mb-3 shadow border-start border-info border-5">
                <div class="section-title">
                    <i class="fa fa-folder-open"></i>
                    <a href="{{ route('loads.load', ['id' => $child->id]) }}">{{ $child->name }}</a> <span class="badge bg-adaptive">{{ $child->count_downs + $child->children->sum('count_downs') }}</span>
                </div>

                <div class="section-body border-top">
                    @if ($child->lastDown)
                        {{ __('loads.down') }}: <a href="{{ route('downs.view', ['id' => $child->lastDown->id]) }}">{{ $child->lastDown->title }}</a>

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
                <div class="section-header d-flex align-items-start">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-file"></i>
                            <a href="{{ route('downs.view', ['id' => $data->id]) }}">{{ $data->title }}</a>
                            @if ($data->isNew())
                                <span class="badge text-bg-success">NEW</span>
                            @endif
                        </div>
                    </div>

                    <div class="text-end section-action js-rating">
                        <span class="badge bg-adaptive">{{ formatNum($data->rating) }}</span>
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

                    <a href="{{ route('downs.comments', ['id' =>  $data->id ]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $data->count_comments }}</span>
                </div>
            </div>
        @endforeach
    @elseif (! $category->closed)
        {{ showError(__('loads.empty_downs')) }}
    @endif

    {{ $downs->links() }}
@stop
