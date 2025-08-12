@extends('layout')

@section('title', __('index.loads'))

@section('header')
    <div class="float-end">
        <a class="btn btn-light" href="{{ route('loads.index') }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.loads') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.loads') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($new)
        <a href="{{ route('admin.downs.new') }}" class="btn btn-success btn-sm">{{ __('loads.pending') }} <span class="badge bg-adaptive">{{ $new }}</span></a>
        <hr>
    @endif

    @if ($categories->isNotEmpty())
        @foreach ($categories as $category)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-folder-open"></i>
                    <a href="{{ route('admin.loads.load', ['id' => $category->id]) }}">{{ $category->name }}</a>
                    @if ($category->new)
                        <span class="badge bg-adaptive">{{ $category->count_downs + $category->children->sum('count_downs') }}/<span style="color:#ff0000">+{{ $category->new->count_downs }}</span></span>
                    @else
                        <span class="badge bg-adaptive">{{ $category->count_downs + $category->children->sum('count_downs') }}</span>
                    @endif

                    @if ($category->closed)
                        <span class="badge bg-danger">{{ __('loads.closed_load') }}</span>
                    @endif

                    @if (isAdmin('boss'))
                        <div class="float-end">
                            <a href="{{ route('admin.loads.edit', ['id' => $category->id]) }}"><i class="fa fa-pencil-alt"></i></a>
                            <a href="{{ route('admin.loads.delete', ['id' => $category->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('loads.confirm_delete_load') }}')"><i class="fa fa-times"></i></a>
                        </div>
                    @endif
                </div>

                <div>
                    @if ($category->children->isNotEmpty())
                        @php $category->children->load('children'); @endphp
                        @foreach ($category->children as $child)
                            <div>
                                <i class="fa fa-angle-right"></i> <b><a href="{{ route('admin.loads.load', ['id' => $child->id]) }}">{{ $child['name'] }}</a></b>
                                @if ($child->new)
                                    <span class="badge bg-adaptive">{{ $child->count_downs + $child->children->sum('count_downs') }}/<span style="color:#ff0000">+{{ $child->new->count_downs }}</span></span>
                                @else
                                    <span class="badge bg-adaptive">{{ $child->count_downs + $child->children->sum('count_downs') }}</span>
                                @endif

                                @if (isAdmin('boss'))
                                    <a href="{{ route('admin.loads.edit', ['id' => $child->id]) }}"><i class="fa fa-pencil-alt"></i></a>
                                    <a href="{{ route('admin.loads.delete', ['id' => $child->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('loads.confirm_delete_load') }}')"><i class="fa fa-times"></i></a>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="section-body border-top">
                    @if ($category->lastDown)
                        {{ __('loads.down') }}: <a href="{{ route('downs.view', ['id' => $category->lastDown->id]) }}">{{ $category->lastDown->title }}</a>

                        @if ($category->lastDown->isNew())
                            <span class="badge text-bg-success">NEW</span>
                        @endif
                        <br>
                        {{ __('main.author') }}: {{ $category->lastDown->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">
                            {{ dateFixed($category->lastDown->created_at) }}
                        </small>
                    @else
                        {{ __('loads.empty_downs') }}
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('loads.empty_loads')) }}
    @endif

    @if (isAdmin('boss'))
        <div class="section-form mb-3 shadow">
            <form action="{{ route('admin.loads.create') }}" method="post">
                @csrf
                <div class="input-group{{ hasError('name') }}">
                    <input type="text" class="form-control" id="name" name="name" maxlength="{{ setting('down_category_max') }}" value="{{ getInput('name') }}" placeholder="{{ __('loads.load') }}" required>
                    <button class="btn btn-primary">{{ __('loads.create_load') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </form>
        </div>

        <i class="fa fa-sync"></i> <a href="{{ route('admin.loads.restatement', ['_token' => csrf_token()]) }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
