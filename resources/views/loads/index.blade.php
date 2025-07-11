@extends('layout')

@section('title', __('index.loads'))

@section('header')
    <div class="float-end">
        @if (isAdmin() || (getUser() && setting('downupload')))
            <a class="btn btn-success" href="{{ route('downs.create') }}">{{ __('main.add') }}</a>

            @if (isAdmin('admin'))
                <a class="btn btn-light" href="{{ route('admin.loads.index') }}"><i class="fas fa-wrench"></i></a>
            @endif
        @endif
    </div>

    <h1>{{ __('index.loads') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.loads') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser())
        {{ __('main.my') }}:
        <a href="{{ route('downs.active-files') }}" class="badge bg-adaptive">{{ __('loads.downs') }}</a>
        <a href="{{ route('downs.active-comments') }}" class="badge bg-adaptive">{{ __('main.comments') }}</a>
    @endif

    {{ __('main.new') }}:
    <a href="{{ route('downs.new-files') }}" class="badge bg-adaptive">{{ __('loads.downs') }}</a>
    <a href="{{ route('downs.new-comments') }}" class="badge bg-adaptive">{{ __('main.comments') }}</a>
    <hr>

    @foreach ($categories as $category)
        <div class="section mb-3 shadow">
            <div class="section-title">
                <i class="fa fa-folder-open"></i>
                <a href="{{ route('loads.load', ['id' => $category->id]) }}">{{ $category->name }}</a>

                @if ($category->new)
                    <span class="badge bg-adaptive">{{ $category->count_downs + $category->children->sum('count_downs') }}/<span style="color:#ff0000">+{{ $category->new->count_downs }}</span></span>
                @else
                    <span class="badge bg-adaptive">{{ $category->count_downs + $category->children->sum('count_downs') }}</span>
                @endif
            </div>

            <div>
                @if ($category->children->isNotEmpty())
                    @php $category->children->load('children'); @endphp
                    @foreach ($category->children as $child)
                        <div>
                            <i class="fa fa-angle-right"></i> <b><a href="{{ route('loads.load', ['id' => $child->id]) }}">{{ $child->name }}</a></b>
                            @if ($child->new)
                                <span class="badge bg-adaptive">{{ $child->count_downs + $child->children->sum('count_downs') }}/<span style="color:#ff0000">+{{ $child->new->count_downs }}</span></span>
                            @else
                                <span class="badge bg-adaptive">{{ $child->count_downs + $child->children->sum('count_downs') }}</span>
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

    <a href="{{ route('loads.rss') }}">{{ __('main.rss') }}</a>
@stop
