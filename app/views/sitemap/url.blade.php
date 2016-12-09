<?php header("Content-type:application/xml; charset=utf-8"); ?>
<?= '<?xml version="1.0" encoding="utf-8"?>' ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

@foreach($locs as $loc)

    <url>
        <loc>{{ $loc['loc'] }}</loc>
        <lastmod>{{ $loc['lastmod'] }}</lastmod>
        <changefreq>{{ $loc['changefreq'] }}</changefreq>
        <priority>{{ $loc['priority'] }}</priority>
    </url>

@endforeach

</urlset>
