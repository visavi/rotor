<div class="progress">
    @if ($percent === 100)
        <div class="progress-bar bg-success" style="width:{{ $percent }}%;">{{ $title }}</div>
    @else
        <div class="progress-bar bg-danger" style="width:{{ $percent }}%;">{{ $title }}</div>
    @endif
</div>
