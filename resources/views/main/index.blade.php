@section('title', __('index.information'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.information') }}</li>
        </ol>
    </nav>
@stop

@php
$tiles = [
    ['url' => '/rules',     'icon' => 'fa-scale-balanced', 'title' => __('index.site_rules'),    'text' => __('index.site_rules_desc')],
    ['url' => '/stickers',  'icon' => 'fa-face-smile',     'title' => __('index.stickers_help'), 'text' => __('index.stickers_help_desc')],
    ['url' => '/faq',       'icon' => 'fa-circle-question','title' => __('index.faq'),           'text' => __('index.faq_desc')],
    ['url' => '/api',       'icon' => 'fa-code',           'title' => __('index.api_interface'), 'text' => __('index.api_interface_desc')],
    ['url' => '/statusfaq', 'icon' => 'fa-award',          'title' => __('index.user_statuses'), 'text' => __('index.user_statuses_desc')],
];
@endphp

<div class="info-tiles">
    @foreach ($tiles as $tile)
        <a class="info-tile" href="{{ $tile['url'] }}">
            <span class="info-tile-icon"><i class="fas {{ $tile['icon'] }}"></i></span>

            <span class="info-tile-body">
                <span class="info-tile-title">{{ $tile['title'] }}</span>
                <span class="info-tile-text">{{ $tile['text'] }}</span>
            </span>

            <i class="fas fa-chevron-right info-tile-arrow"></i>
        </a>
    @endforeach
</div>
