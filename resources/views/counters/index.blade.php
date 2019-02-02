@extends('layout')

@section('title')
    Статистика посещений
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Статистика посещений</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row mb-3">

        <div class="col-md-6 col-12">
            Всего посетителей на сайте: <b>{{ $online[1] }}</b><br>
            Всего авторизованных: <b>{{ $online[0] }}</b><br>
            Всего гостей: <b>{{ ($online[1] - $online[0]) }}</b><br><br>

            Всего хостов: <b>{{ $count->allhosts }}</b><br>
            Всего хитов: <b>{{ $count->allhits }}</b><br>
        </div>

        <div class="col-md-6 col-12">
            Хостов за текущий час: <b>{{ $count->hosts24 }}</b><br>
            Хитов за текущий час: <b>{{ $count->hits24 }}</b><br><br>

            Хостов сегодня: <b>{{ $count->dayhosts }}</b><br>
            Хитов сегодня: <b>{{ $count->dayhits }}</b><br><br>
        </div>
    </div>

    <h3>Динамика за сутки</h3>
    <span class="badge badge-pill badge-primary">Хосты</span>
    <span class="badge badge-pill badge-warning">Хиты</span>

    <div class="ct-chart24 ct-perfect-fourth"></div>

    <h3>Динамика за месяц</h3>
    <span class="badge badge-pill badge-primary">Хосты</span>
    <span class="badge badge-pill badge-warning">Хиты</span>
    <div class="ct-chart31 ct-perfect-fourth"></div>
@stop

@push('styles')
    <link rel="stylesheet" href="/assets/css/chartist.min.css">
    <link rel="stylesheet" href="/assets/css/chartist-plugin-tooltip.css">

    <style>
        .ct-series-a .ct-line,
        .ct-series-a .ct-point {
            stroke: #007bff;
        }

        .ct-series-b .ct-line,
        .ct-series-b .ct-point {
            stroke: #ffc107;
        }

        .ct-labels .ct-horizontal {
            white-space: nowrap;
        }
    </style>
@endpush

@push('scripts')
    <script src="/assets/js/chartist.min.js"></script>
    <script src="/assets/js/chartist-plugin-tooltip.min.js"></script>
    <script>
        new Chartist.Line('.ct-chart31', {
            onlyInteger: true,
            labels: @json($counts31['labels']),
            series: [
                @json($counts31['hits']),
                @json($counts31['hosts'])
            ]
        }, {
            plugins: [
                Chartist.plugins.tooltip()
            ],
            reverseData: true,
            fullWidth: true,
            chartPadding: {
                right: 35
            },
            axisY: {
                onlyInteger: true,
                offset: 35,
                labelInterpolationFnc: function(value) {
                    return (value > 1000) ? (value / 1000) + 'k' : value;
                }
            },
            axisX: {
                labelInterpolationFnc: function (value, index) {
                    return index % 5 === 0 ? value : null;
                }
            }
        }, [
            ['screen and (min-width: 640px)', {
                axisX: {
                    labelInterpolationFnc: function(value, index) {
                        return index % 3 === 0 ? value : null;
                    }
                }
            }]
        ]);

        new Chartist.Line('.ct-chart24', {
            onlyInteger: true,
            labels: @json($counts24['labels']),
            series: [
                @json($counts24['hits']),
                @json($counts24['hosts'])
            ]
        }, {
            plugins: [
                Chartist.plugins.tooltip()
            ],
            reverseData: true,
            fullWidth: true,
            chartPadding: {
                right: 35
            },
            axisY: {
                onlyInteger: true,
                offset: 35
            },
            axisX: {
                labelInterpolationFnc: function (value, index) {
                    return index % 3 === 0 ? value : null;
                }
            }
        });
    </script>
@endpush
