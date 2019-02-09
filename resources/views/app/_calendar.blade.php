<div><b>{{ dateFixed(SITETIME, 'j F Y') }}</b></div>
<table>
    <thead>
        <tr>
            <th>{{ trans('common.mo') }}</th>
            <th>{{ trans('common.tu') }}</th>
            <th>{{ trans('common.we') }}</th>
            <th>{{ trans('common.th') }}</th>
            <th>{{ trans('common.fr') }}</th>
            <th><span class="text-danger">{{ trans('common.sa') }}</span></th>
            <th><span class="text-danger">{{ trans('common.su') }}</span></th>
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
