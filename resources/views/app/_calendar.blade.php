<table>
    <thead><b>{{ dateFixed(SITETIME, 'j F Y') }}</b></thead>
    <thead>
        <tr>
            <th>Пн</th><th>Вт</th><th>Ср</th><th>Чт</th><th>Пт</th><th><span style="color:#ff6666">Сб</span></th><th><span style="color:#ff6666">Вс</span>
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($calendar as $week)
            <tr>
                @foreach ($week as $keyDay => $valDay)
                    @if ($date['day'] == $valDay)
                        <td><b><span style="color: #fff; background-color:#ef8989; padding: 1px 5px;">{{ $valDay }}</span></b></td>
                        @continue
                    @endif

                    @if (in_array($valDay, $newsDays))
                        <td><a href="/news/{{ $newsIds[$valDay] }}"><span style="color: #fff; background-color:#9def89; padding: 1px 5px;">{{ $valDay }}</span></a></td>
                        @continue
                    @endif

                    @if ($keyDay == 5 || $keyDay == 6)
                        <td><span style="color:#ff6666">{{ $valDay }}</span></td>
                        @continue
                    @endif

                    <td>{{ $valDay }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
