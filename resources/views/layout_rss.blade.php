{!! '<?xml version="1.0" encoding="utf-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>@yield('title') - {{ setting('title') }}</title>
        <link>{{ config('app.url') }}/</link>
        <description>RSS - {{ setting('title') }}</description>
        <image>
            <url>{{ config('app.url') }}{{ setting('logotip') }}</url>
            <title>@yield('title') - {{ setting('title') }}</title>
            <link>{{ config('app.url') }}/</link>
        </image>
        <managingEditor>{{ config('app.email') }} ({{ config('app.admin') }})</managingEditor>
        <webMaster>{{ config('app.email') }} ({{ config('app.admin') }})</webMaster>
        <atom:link href="{{ request()->fullUrl() }}" rel="self" type="application/rss+xml" />
        <lastBuildDate>{{ date('r', SITETIME) }}</lastBuildDate>
        @yield('content')
    </channel>
</rss>
