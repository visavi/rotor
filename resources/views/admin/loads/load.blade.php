@extends('layout')

@section('title', $category->name . ' (' . __('main.page_num', ['page' => $downs->currentPage()]) . ')')

@section('header')
    <div class="float-end">
        @if (! $category->closed)
            <a class="btn btn-success" href="/downs/create?cid={{ $category->id }}">{{ __('main.add') }}</a>
        @endif
        <a class="btn btn-light" href="/loads/{{ $category->id }}?page={{ $downs->currentPage() }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ $category->name }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/loads">{{ __('index.loads') }}</a></li>

            @foreach ($category->getParents() as $parent)
                @if ($loop->last)
                    <li class="breadcrumb-item active">{{ $parent->name }}</li>
                @else
                    <li class="breadcrumb-item"><a href="/admin/loads/{{ $parent->id }}">{{ $parent->name }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
@stop

@section('content')
    {{ __('main.sort') }}:

    <?php $active = ($order === 'created_at') ? 'success' : 'adaptive'; ?>
    <a href="/admin/loads/{{ $category->id }}?sort=time" class="badge bg-{{ $active }}">{{ __('main.date') }}</a>

    <?php $active = ($order === 'loads') ? 'success' : 'adaptive'; ?>
    <a href="/admin/loads/{{ $category->id }}?sort=loads" class="badge bg-{{ $active }}">{{ __('main.downloads') }}</a>

    <?php $active = ($order === 'rating') ? 'success' : 'adaptive'; ?>
    <a href="/admin/loads/{{ $category->id }}?sort=rating" class="badge bg-{{ $active }}">{{ __('main.rating') }}</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'adaptive'; ?>
    <a href="/admin/loads/{{ $category->id }}?sort=comments" class="badge bg-{{ $active }}">{{ __('main.comments') }}</a>
    <hr>

    @if ($downs->onFirstPage() && $category->children->isNotEmpty())
        @php $category->children->load(['children', 'lastDown.user']); @endphp
        @foreach ($category->children as $child)
            <div class="section mb-3 shadow border-start border-info border-5">
                <div class="section-title">
                    <i class="fa fa-folder-open"></i>
                    <a href="/admin/loads/{{ $child->id }}">{{ $child->name }}</a> ({{ $child->count_downs }})

                    @if ($child->closed)
                        <span class="badge bg-danger">{{ __('loads.closed_load') }}</span>
                    @endif

                    @if (isAdmin('boss'))
                        <div class="float-end">
                            <a href="/admin/loads/edit/{{ $child->id }}"><i class="fa fa-pencil-alt"></i></a>
                            <a href="/admin/loads/delete/{{ $child->id }}?_token={{ csrf_token() }}" onclick="return confirm('{{ __('loads.confirm_delete_load') }}')"><i class="fa fa-times"></i></a>
                        </div>
                    @endif
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

                    <div class="text-end">
                        <a href="/admin/downs/edit/{{ $data->id }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt"></i></a>

                        @if (isAdmin('boss'))
                            <a href="/admin/downs/delete/{{ $data->id }}?_token={{ csrf_token() }}" onclick="return confirm('{{ __('loads.confirm_delete_down') }}')"><i class="fa fa-times"></i></a>
                        @endif
                    </div>
                </div>

                <div class="section-content">
                    <div class="mb-2">
                        {{ __('main.rating') }}: {{ formatNum($data->rating) }}<br>
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
