<?php header("Content-type:application/rss+xml; charset=utf-8"); ?>

<?= '<?xml version="1.0" encoding="utf-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>
            @section('title')
                {{ Setting::get('title') }}
            @show
        </title>
        <link>{{ Setting::get('home') }}/</link>
        <description>Сообщения RSS - {{ Setting::get('title') }}</description>
        <image>
            <url>{{ Setting::get('home') }}{{ Setting::get('logotip') }}</url>
            <title>Сообщения RSS - {{ Setting::get('title') }}</title>
            <link>{{ Setting::get('home') }}/</link>
        </image>
        <managingEditor>{{ Setting::get('emails') }} ({{ Setting::get('nickname') }})</managingEditor>
        <webMaster>{{ Setting::get('emails') }} ({{ Setting::get('nickname') }})</webMaster>
        <lastBuildDate>{{ date("r", SITETIME) }}</lastBuildDate>

            @yield('content')

    </channel>
</rss>
