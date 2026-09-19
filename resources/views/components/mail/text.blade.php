@props(['muted' => false])

<p class="{{ $muted ? 'muted' : 'text' }}" style="margin: 0 0 14px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: {{ $muted ? '13px' : '15px' }}; line-height: 1.6; color: {{ $muted ? '#636c76' : '#21262c' }};">{{ $slot }}</p>
