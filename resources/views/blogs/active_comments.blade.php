@extends('layout')

@section('title', __('index.blogs') . ' - ' . __('blogs.comments_list', ['user' => $user->getName()]) . ' (' . __('main.page_num', ['page' => $comments->currentPage()])  . ')')

@section('header')
    <h1> {{ __('blogs.comments_list', ['user' => $user->getName()]) }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.comments_list', ['user' => $user->getName()]) }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($comments->isNotEmpty())
        @foreach ($comments as $comment)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-comment"></i>
                    <a href="{{ route('articles.comments', ['id' => $comment->relate_id, 'cid' => $comment->id]) }}">{{ $comment->title }}</a> <span class="badge bg-adaptive">{{ $comment->count_comments }}</span>

                    <div class="float-end">
                        @if (isAdmin())
                            <a href="#" onclick="return deleteComment(this)" data-rid="{{ $comment->relate_id }}" data-id="{{ $comment->id }}" data-type="{{ $comment->relate->getMorphClass() }}" data-bs-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                        @endif
                    </div>
                </div>

                <div class="section-content">
                    <div class="section-message">
                        {{ bbCode($comment->text) }}
                    </div>

                    {{ __('main.posted') }}: {{ $comment->user->getProfile() }} <small class="section-date text-muted fst-italic">{{ dateFixed($comment->created_at) }}</small><br>
                    @if (isAdmin())
                        <div class="small text-muted fst-italic mt-2">
                            {{ $comment->brow }}, {{ $comment->ip }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('main.empty_comments')) }}
    @endif

    {{ $comments->links() }}
@stop
