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
    {{ __('main.sort') }}:

    <?php $active = ($order === 'created_at') ? 'success' : 'light text-dark'; ?>
    <a href="/loads/{{ $category->id }}?sort=time" class="badge bg-{{ $active }}">{{ __('main.date') }}</a>

    <?php $active = ($order === 'loads') ? 'success' : 'light text-dark'; ?>
    <a href="/loads/{{ $category->id }}?sort=loads" class="badge bg-{{ $active }}">{{ __('main.downloads') }}</a>

    <?php $active = ($order === 'rating') ? 'success' : 'light text-dark'; ?>
    <a href="/loads/{{ $category->id }}?sort=rating" class="badge bg-{{ $active }}">{{ __('main.rating') }}</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light text-dark'; ?>
    <a href="/loads/{{ $category->id }}?sort=comments" class="badge bg-{{ $active }}">{{ __('main.comments') }}</a>
    <hr>

    @if ($downs->onFirstPage() && $category->children->isNotEmpty())
        @php $category->children->load('children'); @endphp
        @foreach ($category->children as $child)
            <div class="section mb-3 shadow border-start border-info border-5">
                <div class="section-title">
                    <i class="fa fa-folder-open"></i>
                    <a href="/loads/{{ $child->id }}">{{ $child->name }}</a> ({{ $child->count_downs + $child->children->sum('count_downs') }})
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
                        </div>
                    </div>

                    <div class="text-end js-rating">
                        <b>{{ formatNum($data->rating) }}</b>
                    </div>
                </div>

                <div class="section-content">
                    {{ __('main.downloads') }}: {{ $data->loads }}<br>
                    <a href="/downs/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                    <a href="/downs/end/{{ $data->id }}">&raquo;</a>
                </div>
            </div>
        @endforeach
    @elseif (! $category->closed)
        {{ showError(__('loads.empty_downs')) }}
    @endif

    {{ $downs->links() }}

    <a href="/loads/top">{{ __('loads.top_downs') }}</a> /
    <a href="/loads/search">{{ __('main.search') }}</a>
@stop
