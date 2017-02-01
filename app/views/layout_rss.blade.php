<?php header("Content-type:application/rss+xml; charset=utf-8"); ?>

<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>
            @section('title')
                {{ App::setting('title') }}
            @show
        </title>
        <link>{{ App::setting('home') }}/</link>
        <description>Сообщения RSS - {{ App::setting('title') }}</description>
        <image>
            <url>{{ App::setting('home') }}{{ App::setting('logotip') }}</url>
            <title>Сообщения RSS - {{ App::setting('title') }}</title>
            <link>{{ App::setting('home') }}/</link>
        </image>
        <managingEditor>{{ App::setting('emails') }} ({{ App::setting('nickname') }})</managingEditor>
        <webMaster>{{ App::setting('emails') }} ({{ App::setting('nickname') }})</webMaster>
        <lastBuildDate>{{ date("r", SITETIME) }}</lastBuildDate>

            @yield('content')

    </channel>
</rss>
