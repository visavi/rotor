<div class="font-weight-bold">{{ dateFixed($time, 'j F Y') }}</div>
<table>
    <thead>
        <tr>
            <th class="text-center">{{ trans('main.mo') }}</th>
            <th class="text-center">{{ trans('main.tu') }}</th>
            <th class="text-center">{{ trans('main.we') }}</th>
            <th class="text-center">{{ trans('main.th') }}</th>
            <th class="text-center">{{ trans('main.fr') }}</th>
            <th class="text-center text-danger">{{ trans('main.sa') }}</th>
            <th class="text-center text-danger">{{ trans('main.su') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($calendar as $week)
            <tr>
                @foreach ($week as $keyDay => $valDay)
                    @if ($date['day'] === $valDay)
                        <td class="text-center"><span class="text-white bg-danger px-1 font-weight-bold">{{ $valDay }}</span></td>
                        @continue
                    @endif

                    @if (isset($newsIds[$valDay]))
                        <td class="text-center"><a href="/news/{{ $newsIds[$valDay] }}"><span class="text-white bg-success px-1 font-weight-bold">{{ $valDay }}</span></a></td>
                        @continue
                    @endif

                    @if ($keyDay === 5 || $keyDay === 6)
                        <td class="text-center text-danger">{{ $valDay }}</td>
                        @continue
                    @endif

                    <td class="text-center">{{ $valDay }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
