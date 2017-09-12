@if (setting('incount') == 1)
    <a href="/counter">{{ $count['dayhosts'] }} | {{ $count['allhosts'] }}</a><br>
@endif

@if (setting('incount') == 2)
    <a href="/counter">{{ $count['dayhits'] }} | {{ $count['allhits'] }}</a><br>
@endif

@if (setting('incount') == 3)
    <a href="/counter">{{ $count['dayhosts'] }} | {{ $count['dayhits'] }}</a><br>
@endif

@if (setting('incount') == 4)
    <a href="/counter">{{ $count['allhosts'] }} | {{ $count['allhits'] }}</a><br>
@endif

@if (setting('incount') == 5)
    <a href="/counter"><img src="/uploads/counters/counter.png?{{ dateFixed(SITETIME, "dmYHi") }}" alt="counter"></a><br>
@endif
