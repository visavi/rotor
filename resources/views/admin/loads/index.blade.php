@extends('layout')

@section('title')
    {{ trans('loads.title') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('loads.title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($categories->isNotEmpty())
        @foreach ($categories as $category)
            <div class="b">
                <i class="fa fa-folder-open"></i>
                <b><a href="/admin/loads/{{ $category->id }}">{{ $category->name }}</a></b>
                @if ($category->new)
                    ({{ $category->count_downs }}/<span style="color:#ff0000">+{{ $category->new->count_downs }}</span>)
                @else
                    ({{ $category->count_downs }})
                @endif

                @if (isAdmin('boss'))
                    <div class="float-right">
                        <a href="/admin/loads/edit/{{ $category->id }}"><i class="fa fa-pencil-alt"></i></a>
                        <a href="/admin/loads/delete/{{ $category->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('loads.confirm_delete_load') }}')"><i class="fa fa-times"></i></a>
                    </div>
                @endif
            </div>

            <div>
                @if ($category->children->isNotEmpty())
                    @foreach ($category->children as $child)
                        <i class="fa fa-angle-right"></i> <b><a href="/admin/loads/{{ $child->id }}">{{ $child['name'] }}</a></b>
                        @if ($child->new)
                            ({{ $child->count_downs }}/<span style="color:#ff0000">+{{ $child->new->count_downs }}</span>)
                        @else
                            ({{ $child->count_downs }})
                        @endif

                        @if (isAdmin('boss'))
                                <a href="/admin/loads/edit/{{ $child->id }}"><i class="fa fa-pencil-alt"></i></a>
                                <a href="/admin/loads/delete/{{ $child->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('loads.confirm_delete_load') }}')"><i class="fa fa-times"></i></a>
                        @endif
                        <br>
                    @endforeach
                @endif
            </div>
        @endforeach
    @else
        {!! showError(trans('loads.empty_loads')) !!}
    @endif

    @if (isAdmin('boss'))
        <div class="form my-3">
            <form action="/admin/loads/create" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                <div class="form-inline">
                    <div class="form-group{{ hasError('name') }}">
                        <input type="text" class="form-control" id="name" name="name" maxlength="50" value="{{ getInput('name') }}" placeholder="{{ trans('loads.load') }}" required>
                    </div>

                    <button class="btn btn-primary">{{ trans('loads.create_load') }}</button>
                </div>
                {!! textError('name') !!}
            </form>
        </div>

        <i class="fa fa-sync"></i> <a href="/admin/loads/restatement?token={{ $_SESSION['token'] }}">{{ trans('main.recount') }}</a><br>
    @endif
@stop
