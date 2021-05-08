@extends('layout')

@section('title', __('index.blogs'))

@section('header')
    <div class="float-end">
        <a class="btn btn-light" href="/blogs"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.blogs') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.blogs') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @foreach ($categories as $key => $category)
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-folder-open"></i>
                <a href="/admin/blogs/{{ $category->id }}">{{ $category->name }}</a>

                @if ($category->new)
                    ({{ $category->count_articles }}/<span style="color:#ff0000">+{{ $category->new->count_articles }}</span>)
                @else
                    ({{ $category->count_articles }})
                @endif

                @if (isAdmin('boss'))
                    <div class="float-end">
                        <a href="/admin/blogs/edit/{{ $category->id }}"><i class="fa fa-pencil-alt"></i></a>
                        <a href="/admin/blogs/delete/{{ $category->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('blogs.confirm_delete_blog') }}')"><i class="fa fa-times"></i></a>
                    </div>
                @endif
            </div>

            <div class="section-content">
                @if ($category->children->isNotEmpty())
                    @foreach ($category->children as $child)
                        <i class="fa fa-angle-right"></i>
                        <b><a href="/admin/blogs/{{ $child->id }}">{{ $child->name }}</a></b>

                        @if ($child->new)
                            ({{ $child->count_articles }}/<span style="color:#ff0000">+{{ $child->new->count_articles }}</span>)
                        @else
                            ({{ $child->count_articles }})
                        @endif

                        @if (isAdmin('boss'))
                            <a href="/admin/blogs/edit/{{ $child->id }}"><i class="fa fa-pencil-alt"></i></a>
                            <a href="/admin/blogs/delete/{{ $child->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('blogs.confirm_delete_blog') }}')"><i class="fa fa-times"></i></a>
                        @endif
                        <br>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach

    @if (isAdmin('boss'))
        <div class="section-form my-3 shadow">
            <form action="/admin/blogs/create" method="post">
                @csrf
                <div class="input-group{{ hasError('name') }}">
                    <input type="text" class="form-control" id="name" name="name" maxlength="50" value="{{ getInput('name') }}" placeholder="{{ __('blogs.blog') }}" required>
                    <button class="btn btn-primary">{{ __('main.create') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </form>
        </div>

        <i class="fa fa-sync"></i> <a href="/admin/blogs/restatement?token={{ $_SESSION['token'] }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
