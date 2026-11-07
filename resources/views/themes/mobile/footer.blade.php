<div class="lol" id="down">
    <a href="{{ route('home') }}">{{ setting('copy') }}</a><br>
    {{ showOnline() }}
    {{ showCounter() }}
    <div class="my-1">@include('app/_social')</div>
</div>
<div class="site" style="text-align:center">
    {{ performance() }}
</div>
