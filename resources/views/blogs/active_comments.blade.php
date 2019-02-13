@extends('layout')

@section('title')
    {{ trans('blogs.blogs') }} - {{ trans('blogs.comments_list', ['user' => $user->login]) }} ({{ trans('common.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1> {{ trans('blogs.comments_list', ['user' => $user->login]) }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('blogs.comments_list', ['user' => $user->login]) }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post">
                <div class="b">
                    <i class="fa fa-comment"></i>
                    <b><a href="/articles/comment/{{ $data->relate_id}}/{{ $data->id}}">{{ $data->title }}</a></b> ({{ $data->count_comments }})

                    <div class="float-right">
                        @if (isAdmin())
                            <a href="#" onclick="return deleteComment(this)" data-rid="{{ $data->relate_id }}" data-id="{{ $data->id }}" data-type="{{ App\Models\Blog::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ trans('common.delete') }}"><i class="fa fa-times text-muted"></i></a>
                        @endif
                    </div>
                </div>

                <div>
                    {!! bbCode($data->text) !!}<br>

                    {{ trans('blogs.posted_by') }}: {!! $data->user->getProfile() !!} <small>({{ dateFixed($data->created_at) }})</small><br>
                    @if (isAdmin())
                        <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                    @endif
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('common.empty_comments')) !!}
    @endif
@stop
