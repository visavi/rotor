<ul class="list-inline hiding text-left">
    <li class="list-inline-item" data-toggle="tooltip" title="{{ trans('index.mysql_queries') }}"><i class="fa fa-database"></i> {{ count($queries) }}</li>
    <li class="list-inline-item" data-toggle="tooltip" title="{{ trans('index.ram_consumption') }}"><i class="fa fa-bolt"></i> {{ formatSize(memory_get_usage()) }}</li>

    <?php if (function_exists('sys_getloadavg')): ?>
        <?php $cpu = sys_getloadavg(); ?>
        <li class="list-inline-item" data-toggle="tooltip" title="{{ trans('index.cpu_load') }}"><i class="fa fa-tachometer-alt"></i> {{ round($cpu[0], 2) }}</li>
    <?php endif; ?>

    <li class="list-inline-item" data-toggle="tooltip" title="{{ trans('index.loading_speed') }}"><i class="fa fa-rocket"></i> {{ round(microtime(true) - STARTTIME, 4) }} сек.</li>
</ul>

@if (env('APP_DEBUG'))
<pre class="text-left prettyprint linenums pre-scrollable">
@foreach ($queries as $key=> $query)
{{ $query['query'] }} ({{ number_format($query['time'] /1000, 5) }} сек.)
@endforeach
</pre>
@endif
