<div><a href="/counters">
    <svg xmlns="http://www.w3.org/2000/svg" width="168" height="50" viewBox="0 0 168 50" role="img" aria-label="counter" font-family="inherit">
        <rect fill="var(--base-bg)" stroke="var(--bs-border-color)" x="0.5" y="0.5" width="167" height="49" rx="4"/>
        <text fill="var(--bs-secondary-color)" x="5" y="9" font-size="7" letter-spacing="0.5">{{ $lbl1 }}</text>
        <text fill="var(--bs-body-color)" x="5" y="25" font-size="14" font-weight="700">{{ formatShortNum($val1) }}</text>
        <rect fill="var(--bs-border-color)" x="57" y="6" width="1" height="20"/>
        <text fill="var(--bs-secondary-color)" x="62" y="9" font-size="7" letter-spacing="0.5">{{ $lbl2 }}</text>
        <text fill="var(--bs-body-color)" x="62" y="25" font-size="14" font-weight="700">{{ formatShortNum($val2) }}</text>
        <rect fill="var(--bs-border-color)" x="114" y="6" width="1" height="20"/>
        <text fill="var(--bs-secondary-color)" x="119" y="9" font-size="7" letter-spacing="0.5">{{ __('main.online') }}</text>
        <circle cx="161" cy="7" r="3.5" fill="#22c55e" opacity="0.2"/>
        <circle cx="161" cy="7" r="2" fill="#22c55e"/>
        <text fill="var(--bs-body-color)" x="119" y="25" font-size="14" font-weight="700">{{ formatShortNum($online) }}</text>
        @foreach($bars as $i => $bar)
            <rect fill="{{ $bar['c'] }}" x="{{ 5 + $i * 23 }}" y="{{ 45 - $bar['h'] }}" width="20" height="{{ $bar['h'] }}" rx="1"/>
            <text fill="#ffffff" x="{{ 15 + $i * 23 }}" y="44" font-size="6" text-anchor="middle" font-weight="700">{{ $bar['l'] }}</text>
        @endforeach
    </svg>
</a></div>
