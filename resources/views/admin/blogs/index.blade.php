@extends('layout')

@section('title', __('index.blogs'))

@section('header')
    <div class="float-end">
        <a class="btn btn-light" href="{{ route('blogs.index') }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.blogs') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.blogs') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($new)
        <a href="{{ route('admin.articles.new') }}" class="btn btn-success btn-sm">{{ __('loads.pending') }} <span class="badge bg-adaptive">{{ $new }}</span></a>
        <hr>
    @endif

    @foreach ($categories as $key => $category)
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-folder-open"></i>
                <a href="{{ route('admin.blogs.blog', ['id' => $category->id]) }}">{{ $category->name }}</a>

                @if ($category->new)
                    <span class="badge bg-adaptive">{{ $category->count_articles }}/<span style="color:#ff0000">+{{ $category->new->count_articles }}</span></span>
                @else
                    <span class="badge bg-adaptive">{{ $category->count_articles }}</span>
                @endif

                @if (isAdmin('boss'))
                    <div class="float-end">
                        <a href="{{ route('admin.blogs.edit', ['id' => $category->id]) }}"><i class="fa fa-pencil-alt"></i></a>
                        <a href="{{ route('admin.blogs.delete', ['id' => $category->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('blogs.confirm_delete_blog') }}')"><i class="fa fa-times"></i></a>
                    </div>
                @endif
            </div>

            <div class="section-content">
                @if ($category->children->isNotEmpty())
                    @foreach ($category->children as $child)
                        <div>
                            <i class="fa fa-angle-right"></i>
                            <b><a href="{{ route('admin.blogs.blog', ['id' => $child->id]) }}">{{ $child->name }}</a></b>

                            @if ($child->new)
                                <span class="badge bg-adaptive">{{ $child->count_articles }}/<span style="color:#ff0000">+{{ $child->new->count_articles }}</span></span>
                            @else
                                <span class="badge bg-adaptive">{{ $child->count_articles }}</span>
                            @endif

                            @if (isAdmin('boss'))
                                <a href="{{ route('admin.blogs.edit', ['id' => $child->id]) }}"><i class="fa fa-pencil-alt"></i></a>
                                <a href="{{ route('admin.blogs.delete', ['id' => $child->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('blogs.confirm_delete_blog') }}')"><i class="fa fa-times"></i></a>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="section-body border-top">
                @if ($category->lastArticle)
                    {{ __('blogs.article') }}: <a href="{{ route('articles.view', ['slug' => $category->lastArticle->slug]) }}">{{ $category->lastArticle->title }}</a>

                    @if ($category->lastArticle->isNew())
                        <span class="badge text-bg-success">NEW</span>
                    @endif
                    <br>
                    {{ __('main.author') }}: {{ $category->lastArticle->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">
                        {{ dateFixed($category->lastArticle->created_at) }}
                    </small>
                @else
                    {{ __('blogs.empty_articles') }}
                @endif
            </div>
        </div>
    @endforeach

    @if (isAdmin('boss'))
        <div class="section-form my-3 shadow">
            <form action="{{ route('admin.blogs.create') }}" method="post">
                @csrf
                <div class="input-group{{ hasError('name') }}">
                    <input type="text" class="form-control" id="name" name="name" maxlength="{{ setting('blog_category_max') }}" value="{{ getInput('name') }}" placeholder="{{ __('blogs.blog') }}" required>
                    <button class="btn btn-primary">{{ __('main.create') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </form>
        </div>

        <i class="fa fa-sync"></i> <a href="{{ route('admin.blogs.restatement', ['_token' => csrf_token()]) }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
