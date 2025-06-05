@extends('layout')

@section('title', __('index.offers'))

@section('header')
    <div class="float-end">
        @if (getUser())
            <a class="btn btn-success" href="/offers/create?type={{ $type }}">{{ __('main.add') }}</a>

            @if (isAdmin())
                <a class="btn btn-light" href="/admin/offers/{{ $type }}?page={{ $offers->currentPage() }}"><i class="fas fa-wrench"></i></a>
            @endif
        @endif
    </div>

    <h1>{{ __('index.offers') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.offers') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <?php $active = ($type === 'offer') ? 'primary' : 'adaptive'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="/offers/offer?sort={{ $sort }}&amp;order={{ $order }}">{{ __('offers.offers') }} <span class="badge bg-adaptive">{{ $offerCount }}</span></a>

        <?php $active = ($type === 'issue') ? 'primary' : 'adaptive'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="/offers/issue?sort={{ $sort }}&amp;order={{ $order }}">{{ __('offers.problems') }} <span class="badge bg-adaptive">{{ $issueCount }}</span></a>
    </div>

    @if ($offers->isNotEmpty())
        <div class="sort-links border-bottom pb-3 mb-3">
            {{ __('main.sort') }}:
            @foreach ($sorting as $key => $option)
                <a href="{{ route('offers.index', ['type' => $type, 'sort' => $key, 'order' => $option['inverse'] ?? 'desc']) }}" class="badge bg-{{ $option['badge'] ?? 'adaptive' }}">
                    {{ $option['label'] }}{{ $option['icon'] ?? '' }}
                </a>
            @endforeach
        </div>

        @foreach ($offers as $data)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-file"></i>
                    <a href="/offers/{{ $data->id }}">{{ $data->title }}</a> ({{ __('main.votes') }}: {{ $data->rating }})<br>
                </div>

                <div class="section-body">
                    {{ $data->getStatus() }}<br>
                    {{ bbCode($data->text) }}<br>
                    {{ __('main.added') }}: {{ $data->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small><br>
                    <a href="/offers/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                    <a href="/offers/end/{{ $data->id }}">&raquo;</a>
                </div>
            </div>
        @endforeach

        {{ $offers->links() }}

        <div class="mb-3">
            {{ __('main.total') }}: <b>{{ $offers->total() }}</b>
        </div>
    @else
        {{ showError(__('main.empty_records')) }}
    @endif
@stop
