@props(['url', 'color' => 'primary'])

@php
    $colors = [
        'primary' => '#0d6efd',
        'success' => '#198754',
        'error'   => '#dc3545',
    ];
    $background = $colors[$color] ?? $colors['primary'];
@endphp

<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 24px 0;">
    <tr>
        <td align="center" bgcolor="{{ $background }}" style="border-radius: 8px;">
            <a href="{{ $url }}" class="btn" target="_blank" rel="noopener" style="display: inline-block; padding: 13px 28px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 600; line-height: 20px; color: #ffffff; text-decoration: none; border-radius: 8px; background-color: {{ $background }};">{{ $slot }}</a>
        </td>
    </tr>
</table>
