@if ($courses)

    <b>Курсы валют</b> ({{ $courses->Date }})<br>
    <b>{{ $courses->USD->nominal }} {{ $courses->USD->name }}</b> - {{ $courses->USD->value }}<br>
    <b>{{ $courses->EUR->nominal }} {{ $courses->EUR->name }}</b> - {{ $courses->EUR->value }}<br>
    <b>{{ $courses->UAH->nominal }} {{ $courses->UAH->name }}</b> - {{ $courses->UAH->value }}<br>

@else
    {!! showError('Ошибка! Не удалось загрузить последние курсы валют!') !!}
@endif
