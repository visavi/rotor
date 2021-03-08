@extends('layout')

@section('title', __('forums.title_bookmarks'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_bookmarks') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($topics->isNotEmpty())
        <form action="/forums/bookmarks/delete?page={{ $topics->currentPage() }}" method="post">
            @csrf
            @foreach ($topics as $topic)
                <div class="section mb-3 shadow">
                    <input type="checkbox" name="del[]" value="{{ $topic->id }}">

                    <i class="fa {{ $topic->topic->getIcon() }} text-muted"></i>
                    <b><a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a></b>
                    ({{ $topic->count_posts }}{!! ($topic->count_posts > $topic->bookmark_posts) ? '/<span style="color:#00cc00">+' . ($topic->count_posts - $topic->bookmark_posts) . '</span>' : '' !!})

                    {!! $topic->topic->pagination() !!}
                    {{ __('main.author') }}: {{ $topic->topic->user->getName() }} /
                    {{ __('forums.latest') }}: {{ $topic->topic->lastPost->user->getName() }}
                    ({{ dateFixed($topic->topic->lastPost->created_at) }})
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
        </form>
    @else
        {{ showError(__('forums.empty_bookmarks')) }}
    @endif

    {{ $topics->links() }}
@stop
