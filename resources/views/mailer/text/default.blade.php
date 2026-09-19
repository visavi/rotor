{!! trim(html_entity_decode(strip_tags(preg_replace('#<br\s*/?>|</p>|</div>#i', "\n", $text)), ENT_QUOTES | ENT_HTML5)) !!}

--
{{ setting('copy') }}
{{ config('app.url') }}
