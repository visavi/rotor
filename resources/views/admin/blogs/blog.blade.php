@extends('layout')

@section('title', $category->name . ' (' . __('main.page_num', ['page' => $articles->currentPage()]) . ')')

@section('header')
    <div class="float-end">
        @if (! $category->closed)
            <a class="btn btn-success" href="{{ route('blogs.create', ['cid' => $category->id]) }}">{{ __('blogs.add') }}</a>
        @endif

        <a class="btn btn-light" href="{{ route('blogs.blog', ['id' => $category->id, 'page' => $articles->currentPage()]) }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ $category->name }} <small>({{ __('blogs.all_articles') }}: {{ $category->count_articles }})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">{{ __('index.blogs') }}</a></li>

            @foreach ($category->getParents() as $parent)
                @if ($loop->last)
                    <li class="breadcrumb-item active">{{ $parent->name }}</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ route('admin.blogs.blog', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
@stop

@section('content')
    @if ($articles->onFirstPage() && $category->children->isNotEmpty())
        @php $category->children->load(['children', 'lastArticle.user']); @endphp
        @foreach ($category->children as $child)
            <div class="section mb-3 shadow border-start border-info border-5">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-file-alt fa-lg text-muted"></i>
                            <a href="{{ route('admin.blogs.blog', ['id' => $child->id]) }}">{{ $child->name }}</a>
                            <span class="badge bg-adaptive">{{ $child->count_articles }}</span>
                        </div>
                    </div>

                    @if (isAdmin('boss'))
                        <div class="float-end">
                            <a href="{{ route('admin.blogs.edit', ['id' => $category->id]) }}"><i class="fa fa-pencil-alt"></i></a>
                            <a href="{{ route('admin.blogs.delete', ['id' => $category->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('blogs.confirm_delete_blog') }}')"><i class="fa fa-times"></i></a>
                        </div>
                    @endif
                </div>

                <div class="section-body border-top">
                    @if ($child->lastArticle)
                        {{ __('blogs.article') }}: <a href="{{ route('articles.view', ['slug' => $child->lastArticle->slug]) }}">{{ $child->lastArticle->title }}</a>

                        @if ($child->lastArticle->isNew())
                            <span class="badge text-bg-success">NEW</span>
                        @endif
                        <br>
                        {{ __('main.author') }}: {{ $child->lastArticle->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">
                            {{ dateFixed($child->lastArticle->created_at) }}
                        </small>
                    @else
                        {{ __('blogs.empty_articles') }}
                    @endif
                </div>
            </div>
        @endforeach
        <hr>
    @endif

    @if ($articles->isNotEmpty())
        @foreach ($articles as $article)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-pencil-alt"></i>
                            <a href="{{ route('articles.view', ['slug' => $article->slug]) }}">{{ $article->title }}</a>
                            @if ($article->isNew())
                                <span class="badge text-bg-success">NEW</span>
                            @endif
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.articles.edit', ['id' => $article->id]) }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        <a href="{{ route('admin.articles.delete', ['id' => $article->id, '_token' => csrf_token(), 'page' => $articles->currentPage()]) }}" onclick="return confirm('{{ __('blogs.confirm_delete_article') }}')" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                    </div>
                </div>

                <div class="section-content">
                    <div class="section-message">
                        {{ $article->shortText() }}
                    </div>

                    <div class="mb-2">
                        {{ __('main.rating') }}: {{ formatNum($article->rating) }}<br>
                        {{ __('main.views') }}: {{ $article->visits }}<br>
                        {{ __('main.author') }}: {{ $article->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">
                            {{ dateFixed($article->created_at) }}
                        </small>
                    </div>
                    <a href="{{ route('articles.comments', ['id' => $article->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $article->count_comments }}</span>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('blogs.empty_articles')) }}
    @endif

    {{ $articles->links() }}
@stop
