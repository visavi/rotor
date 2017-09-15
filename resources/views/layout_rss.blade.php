<?php header("Content-type:application/rss+xml; charset=utf-8"); ?>

<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>@yield('title') {{ setting('title') }}</title>
        <link>{{ setting('home') }}/</link>
        <description>Сообщения RSS - {{ setting('title') }}</description>
        <image>
            <url>{{ setting('home') }}{{ setting('logotip') }}</url>
            <title>Сообщения RSS - {{ setting('title') }}</title>
            <link>{{ setting('home') }}/</link>
        </image>
        <managingEditor>{{ setting('emails') }} ({{ setting('nickname') }})</managingEditor>
        <webMaster>{{ setting('emails') }} ({{ setting('nickname') }})</webMaster>
        <lastBuildDate>{{ date("r", SITETIME) }}</lastBuildDate>

            @yield('content')

    </channel>
</rss>
