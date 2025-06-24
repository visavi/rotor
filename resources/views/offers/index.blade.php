@extends('layout')

@section('title', __('index.offers'))

@section('header')
    <div class="float-end">
        @if (getUser())
            <a class="btn btn-success" href="{{ route('offers.create', ['type' => $type]) }}">{{ __('main.add') }}</a>

            @if (isAdmin())
                <a class="btn btn-light" href="{{ route('admin.offers.index', ['type' => $type, 'page' => $offers->currentPage()]) }}"><i class="fas fa-wrench"></i></a>
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
        <a class="btn btn-{{ $active }} btn-sm" href="{{ route('offers.index', ['type' => 'offer', 'sort' => $sort, 'order' => $order]) }}">{{ __('offers.offers') }} <span class="badge bg-adaptive">{{ $offerCount }}</span></a>

        <?php $active = ($type === 'issue') ? 'primary' : 'adaptive'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="{{ route('offers.index', ['type' => 'issue', 'sort' => $sort, 'order' => $order]) }}">{{ __('offers.problems') }} <span class="badge bg-adaptive">{{ $issueCount }}</span></a>
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
                <div class="float-end js-rating">
                    @if (getUser() && getUser('id') !== $data->user_id)
                        <a class="post-rating-down{{ $data->poll?->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-down"></i></a>
                    @endif
                    <b>{{ formatNum($data->rating) }}</b>
                    @if (getUser() && getUser('id') !== $data->user_id)
                        <a class="post-rating-up{{ $data->poll?->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data->id }}" data-type="{{ $data->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-up"></i></a>
                    @endif
                </div>

                <div class="section-title">
                    <i class="fa fa-file"></i>
                    <a href="{{ route('offers.view', ['id' => $data->id]) }}">{{ $data->title }}</a>
                </div>

                <div class="section-body">
                    {{ $data->getStatus() }}<br>
                    {{ bbCode($data->text) }}<br>
                    {{ __('main.added') }}: {{ $data->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small><br>
                    <a href="{{ route('offers.comments', ['id' => $data->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $data->count_comments }}</span>
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
