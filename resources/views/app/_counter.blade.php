@if (setting('incount') === '1')
    <a href="/counters">{{ $count->dayhosts }} | {{ $count->allhosts }}</a><br>
@endif

@if (setting('incount') === '2')
    <a href="/counters">{{ $count->dayhits }} | {{ $count->allhits }}</a><br>
@endif

@if (setting('incount') === '3')
    <a href="/counters">{{ $count->dayhosts }} | {{ $count->dayhits }}</a><br>
@endif

@if (setting('incount') === '4')
    <a href="/counters">{{ $count->allhosts }} | {{ $count->allhits }}</a><br>
@endif

@if (setting('incount') === '5')
    <a href="/counters"><img src="/uploads/counters/counter.png?{{ dateFixed(SITETIME, 'dmYHi') }}" alt="counter"></a><br>
@endif
