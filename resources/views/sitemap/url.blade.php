<?xml version="1.0" encoding="utf-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($locs as $loc)
    <url>
        <loc>{{ $loc['loc'] }}</loc>
        <lastmod>{{ $loc['lastmod'] }}</lastmod>
    </url>
@endforeach
</urlset>
