<div class="alert alert-light pb-0 my-3 cursor-pointer" onclick="return showQueries();">
    <ul class="list-inline">
        <li class="list-inline-item" data-toggle="tooltip" title="{{ __('index.db_queries') }}"><i class="fas fa-database"></i> {{ count($queries) }}</li>

        <li class="list-inline-item" data-toggle="tooltip" title="{{ __('index.db_speed') }}"><i class="fas fa-clock"></i> {{ number_format($timeQueries / 1000, 3) }} {{ __('index.seconds') }}</li>

        <li class="list-inline-item" data-toggle="tooltip" title="{{ __('index.ram_consumption') }}"><i class="fas fa-bolt"></i> {{ formatSize(memory_get_usage()) }}</li>

        <?php if (function_exists('sys_getloadavg')): ?>
            <?php $cpu = sys_getloadavg(); ?>
            <li class="list-inline-item" data-toggle="tooltip" title="{{ __('index.cpu_load') }}"><i class="fas fa-tachometer-alt"></i> {{ round($cpu[0], 2) }}</li>
        <?php endif; ?>

        <li class="list-inline-item" data-toggle="tooltip" title="{{ __('index.loading_speed') }}"><i class="fas fa-rocket"></i> {{ round(microtime(true) - STARTTIME, 3) }} {{ __('index.seconds') }}</li>
    </ul>

@if (config('APP_DEBUG'))
<pre class="js-queries text-left prettyprint linenums pre-scrollable" style="display: none">
@foreach ($queries as $key=> $query)
{{ $query['query'] }} ({{ number_format($query['time'] / 1000, 4) }} {{ __('index.seconds') }})
@endforeach
</pre>
@endif
</div>
