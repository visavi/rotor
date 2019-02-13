@extends('layout')

@section('title')
    {{ trans('forums.forum') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('forums.forum') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('forums.title_bookmarks') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->isNotEmpty())
        <form action="/forums/bookmarks/delete?page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            @foreach ($topics as $topic)
                <div class="b">
                    <input type="checkbox" name="del[]" value="{{ $topic->id }}">

                    <i class="fa {{ $topic->topic->getIcon() }} text-muted"></i>
                    <b><a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a></b>
                    ({{ $topic->count_posts }}{!! ($topic->count_posts > $topic->bookmark_posts) ? '/<span style="color:#00cc00">+' . ($topic->count_posts - $topic->bookmark_posts) . '</span>' : '' !!})
                </div>

                <div>
                    {!! $topic->topic->pagination() !!}
                    {{ trans('main.author') }}: {{ $topic->topic->user->getName() }} /
                    {{ trans('forums.latest') }}: {{ $topic->topic->lastPost->user->getName() }}
                    ({{ dateFixed($topic->topic->lastPost->created_at) }})
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">{{ trans('main.delete_selected') }}</button>
        </form>

        {!! pagination($page) !!}
    @else
        {!! showError(trans('forums.empty_bookmarks')) !!}
    @endif
@stop
