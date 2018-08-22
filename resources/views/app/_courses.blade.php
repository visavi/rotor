@if ($courses)

    <b>Курсы валют</b> ({{ date('d.m.Y', strtotime($courses->Date)) }})<br>
    <b>{{ $courses->Valute->USD->Nominal }} {{ $courses->Valute->USD->Name }}</b> - {{ $courses->Valute->USD->Value }}<br>
    <b>{{ $courses->Valute->EUR->Nominal }} {{ $courses->Valute->EUR->Name }}</b> - {{ $courses->Valute->EUR->Value }}<br>
    <b>{{ $courses->Valute->UAH->Nominal }} {{ $courses->Valute->UAH->Name }}</b> - {{ $courses->Valute->UAH->Value }}<br>

@else
    {!! showError('Ошибка! Не удалось загрузить последние курсы валют!') !!}
@endif
