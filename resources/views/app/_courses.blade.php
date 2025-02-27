<?php
$trend = static function ($currency) {
    if ($currency['Value'] > $currency['Previous']) {
        return ' <i class="fas fa-caret-up text-success"></i>';
    }
    if ($currency['Value'] < $currency['Previous']) {
        return ' <i class="fas fa-caret-down text-danger"></i>';
    }

    return '';
};
?>

@if ($courses)
    <b>{{ __('index.courses') }}</b> ({{ date('d.m.Y', strtotime($courses['Date'])) }})<br>
    <b>{{ $courses['Valute']['USD']['Nominal'] }} {{ $courses['Valute']['USD']['CharCode'] }}</b> - {{ $courses['Valute']['USD']['Value'] }}{!! $trend($courses['Valute']['USD']) !!}
    <br>
    <b>{{ $courses['Valute']['EUR']['Nominal'] }} {{ $courses['Valute']['EUR']['CharCode'] }}</b> - {{ $courses['Valute']['EUR']['Value'] }}{!! $trend($courses['Valute']['EUR']) !!}
    <br>
    <b>{{ $courses['Valute']['UAH']['Nominal'] }} {{ $courses['Valute']['UAH']['CharCode'] }}</b> - {{ $courses['Valute']['UAH']['Value'] }}{!! $trend($courses['Valute']['UAH']) !!}
    <br>

@else
    {{ showError(__('index.courses_error')) }}
@endif
