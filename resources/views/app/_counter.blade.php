@if (setting('incount') === 1)
    <div><a href="/counters">{{ $counter['dayhosts'] }} | {{ $counter['allhosts'] }}</a></div>
@elseif (setting('incount') === 2)
    <div><a href="/counters">{{ $counter['dayhits'] }} | {{ $counter['allhits'] }}</a></div>
@elseif (setting('incount') === 3)
    <div><a href="/counters">{{ $counter['dayhosts'] }} | {{ $counter['dayhits'] }}</a></div>
@elseif (setting('incount') === 4)
    <div><a href="/counters">{{ $counter['allhosts'] }} | {{ $counter['allhits'] }}</a></div>
@elseif (setting('incount') === 5)
    <div><a href="/counters"><img src="/uploads/counters/counter.png?{{ date('YmdHi') }}" alt="counter"></a></div>
@endif
