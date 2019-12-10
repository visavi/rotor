@if (setting('incount') === 1)
    <a href="/counters">{{ $counter['dayhosts'] }} | {{ $counter['allhosts'] }}</a><br>
@endif

@if (setting('incount') === 2)
    <a href="/counters">{{ $counter['dayhits'] }} | {{ $counter['allhits'] }}</a><br>
@endif

@if (setting('incount') === 3)
    <a href="/counters">{{ $counter['dayhosts'] }} | {{ $counter['dayhits'] }}</a><br>
@endif

@if (setting('incount') === 4)
    <a href="/counters">{{ $counter['allhosts'] }} | {{ $counter['allhits'] }}</a><br>
@endif

@if (setting('incount') === 5)
    <a href="/counters"><img src="/uploads/counters/counter.png?{{ dateFixed(SITETIME, 'YmdHi') }}" alt="counter"></a><br>
@endif
