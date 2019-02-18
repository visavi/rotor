@if ($courses)

    <b>{{ trans('index.courses') }}</b> ({{ date('d.m.Y', strtotime($courses->Date)) }})<br>
    <b>{{ $courses->Valute->USD->Nominal }} {{ $courses->Valute->USD->Name }}</b> - {{ $courses->Valute->USD->Value }}<br>
    <b>{{ $courses->Valute->EUR->Nominal }} {{ $courses->Valute->EUR->Name }}</b> - {{ $courses->Valute->EUR->Value }}<br>
    <b>{{ $courses->Valute->UAH->Nominal }} {{ $courses->Valute->UAH->Name }}</b> - {{ $courses->Valute->UAH->Value }}<br>

@else
    {!! showError(trans('index.courses_error')) !!}
@endif
