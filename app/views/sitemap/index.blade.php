<?php header("Content-type:application/xml; charset=utf-8"); ?>
<?= '<?xml version="1.0" encoding="utf-8"?>' ?>

<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

@foreach($locs as $loc)

    <sitemap>
        <loc>{{ $loc['loc'] }}</loc>
        <lastmod>{{ $loc['lastmod'] }}</lastmod>
    </sitemap>

@endforeach

</sitemapindex>
