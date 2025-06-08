@extends('layout_rss')

@section('title', __('news.rss_title'))

@section('content')
    @foreach ($newses as $news)
        <?php $news->text = bbCode($news->text); ?>
        <?php $news->text = str_replace(['/uploads/stickers'], [config('app.url') . '/uploads/stickers'], $news->text); ?>
        <item>
            <title>{{ $news->title }}</title>
            <link>{{ route('news.view', ['id' => $news->id]) }}</link>
            <description>{{ $news->text }}</description>
            <author>{{ $news->user->getName() }}</author>
            <pubDate>{{ date('r', $news->created_at) }}</pubDate>
            <category>{{ __('index.news') }}</category>
            <guid>{{ route('news.view', ['id' => $news->id]) }}</guid>
        </item>
    @endforeach
@stop
