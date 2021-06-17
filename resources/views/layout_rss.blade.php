<?php header('Content-type:application/rss+xml; charset=utf-8'); ?>

<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>@yield('title') - {{ setting('title') }}</title>
        <link>{{ config('app.url') }}/</link>
        <description>RSS - {{ setting('title') }}</description>
        <image>
            <url>{{ config('app.url') }}{{ setting('logotip') }}</url>
            <title>RSS - {{ setting('title') }}</title>
            <link>{{ config('app.url') }}/</link>
        </image>
        <managingEditor>{{ config('app.email') }} ({{ config('app.admin') }})</managingEditor>
        <webMaster>{{ config('app.email') }} ({{ config('app.admin') }})</webMaster>
        <lastBuildDate>{{ date('r', SITETIME) }}</lastBuildDate>
        @yield('content')
    </channel>
</rss>
