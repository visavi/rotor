@extends('layout_rss')

@section('title', __('loads.rss_downs'))

@section('content')
    @foreach ($downs as $down)
        <?php $downText = absolutizeUrls((string) $down->getText()); ?>

        <item>
            <title>{{ $down->title }}</title>
            <link>{{ route('downs.view', ['id' => $down->id]) }}</link>
            <description>{{ $downText }}</description>
            <dc:creator>{{ $down->user->getName() }}</dc:creator>
            <pubDate>{{ date('r', $down->created_at) }}</pubDate>
            <category>{{ __('index.loads') }}</category>
            <guid>{{ route('downs.view', ['id' => $down->id]) }}</guid>
        </item>
    @endforeach
@stop
