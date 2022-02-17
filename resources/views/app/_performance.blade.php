<?php $cursor = config('app.debug') ? 'pointer' : 'default'; ?>
<div class="performance" onclick="return showQueries();" style="cursor: {{ $cursor }}">
    <ul class="list-inline">
        <li class="list-inline-item" data-bs-toggle="tooltip" title="{{ __('index.db_queries') }}"><i class="fas fa-database"></i> {{ count($queries) }}</li>

        <li class="list-inline-item" data-bs-toggle="tooltip" title="{{ __('index.db_speed') }}"><i class="fas fa-clock"></i> {{ number_format($timeQueries / 1000, 3) }} {{ __('index.seconds') }}</li>

        <li class="list-inline-item" data-bs-toggle="tooltip" title="{{ __('index.ram_consumption') }}"><i class="fas fa-bolt"></i> {{ formatSize(memory_get_usage()) }}</li>

        <?php if (function_exists('sys_getloadavg')): ?>
            <?php $cpu = sys_getloadavg(); ?>
            <li class="list-inline-item" data-bs-toggle="tooltip" title="{{ __('index.cpu_load') }}"><i class="fas fa-gauge-high"></i> {{ round($cpu[0], 2) }}</li>
        <?php endif; ?>

        <li class="list-inline-item" data-bs-toggle="tooltip" title="{{ __('index.loading_speed') }}"><i class="fas fa-rocket"></i> {{ round(microtime(true) - LARAVEL_START, 3) }} {{ __('index.seconds') }}</li>
    </ul>

@if (config('app.debug'))
<pre class="js-queries text-start prettyprint linenums pre-scrollable" style="display: none">
@foreach ($queries as $key => $query)
{{ $query['query'] }} ({{ number_format($query['time'] / 1000, 4) }} {{ __('index.seconds') }})
@endforeach
</pre>
@endif
</div>
