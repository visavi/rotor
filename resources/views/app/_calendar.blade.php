<div><b>{{ dateFixed($time, 'j F Y') }}</b></div>
<table>
    <thead>
        <tr>
            <th>{{ trans('main.mo') }}</th>
            <th>{{ trans('main.tu') }}</th>
            <th>{{ trans('main.we') }}</th>
            <th>{{ trans('main.th') }}</th>
            <th>{{ trans('main.fr') }}</th>
            <th><span class="text-danger">{{ trans('main.sa') }}</span></th>
            <th><span class="text-danger">{{ trans('main.su') }}</span></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($calendar as $week)
            <tr>
                @foreach ($week as $keyDay => $valDay)
                    @if ($date['day'] === $valDay)
                        <td><b><span style="color: #fff; background-color:#ef8989; padding: 1px 5px;">{{ $valDay }}</span></b></td>
                        @continue
                    @endif

                    @if (in_array($valDay, $newsDays, true))
                        <td><a href="/news/{{ $newsIds[$valDay] }}"><span style="color: #fff; background-color:#9def89; padding: 1px 5px;">{{ $valDay }}</span></a></td>
                        @continue
                    @endif

                    @if ($keyDay === 5 || $keyDay === 6)
                        <td><span class="text-danger">{{ $valDay }}</span></td>
                        @continue
                    @endif

                    <td>{{ $valDay }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
