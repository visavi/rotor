@extends('layout')

@section('title', __('index.loads'))

@section('header')
    <div class="float-end">
        <a class="btn btn-light" href="/loads"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.loads') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.loads') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($categories->isNotEmpty())
        @foreach ($categories as $category)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-folder-open"></i>
                    <a href="/admin/loads/{{ $category->id }}">{{ $category->name }}</a>
                    @if ($category->new)
                        ({{ $category->count_downs }}/<span style="color:#ff0000">+{{ $category->new->count_downs }}</span>)
                    @else
                        ({{ $category->count_downs }})
                    @endif

                    @if ($category->closed)
                        <span class="badge bg-danger">{{ __('loads.closed_load') }}</span>
                    @endif

                    @if (isAdmin('boss'))
                        <div class="float-end">
                            <a href="/admin/loads/edit/{{ $category->id }}"><i class="fa fa-pencil-alt"></i></a>
                            <a href="/admin/loads/delete/{{ $category->id }}?_token={{ csrf_token() }}" onclick="return confirm('{{ __('loads.confirm_delete_load') }}')"><i class="fa fa-times"></i></a>
                        </div>
                    @endif
                </div>

                <div>
                    @if ($category->children->isNotEmpty())
                        @foreach ($category->children as $child)
                            <div>
                                <i class="fa fa-angle-right"></i> <b><a href="/admin/loads/{{ $child->id }}">{{ $child['name'] }}</a></b>
                                @if ($child->new)
                                    ({{ $child->count_downs }}/<span style="color:#ff0000">+{{ $child->new->count_downs }}</span>)
                                @else
                                    ({{ $child->count_downs }})
                                @endif

                                @if (isAdmin('boss'))
                                    <a href="/admin/loads/edit/{{ $child->id }}"><i class="fa fa-pencil-alt"></i></a>
                                    <a href="/admin/loads/delete/{{ $child->id }}?_token={{ csrf_token() }}" onclick="return confirm('{{ __('loads.confirm_delete_load') }}')"><i class="fa fa-times"></i></a>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('loads.empty_loads')) }}
    @endif

    @if (isAdmin('boss'))
        <div class="section-form mb-3 shadow">
            <form action="/admin/loads/create" method="post">
                @csrf
                <div class="input-group{{ hasError('name') }}">
                    <input type="text" class="form-control" id="name" name="name" maxlength="50" value="{{ getInput('name') }}" placeholder="{{ __('loads.load') }}" required>
                    <button class="btn btn-primary">{{ __('loads.create_load') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </form>
        </div>

        <i class="fa fa-sync"></i> <a href="/admin/loads/restatement?_token={{ csrf_token() }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
