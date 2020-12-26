@extends('layout')

@section('title', __('index.offers'))

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/offers/create?type={{ $type }}">{{ __('main.add') }}</a>
        </div>
    @endif

    <h1>{{ __('index.offers') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.offers') }}</li>

            @if (isAdmin('admin'))
                <li class="breadcrumb-item"><a href="/admin/offers/{{ $type }}?page={{ $offers->currentPage() }}">{{ __('main.management') }}</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if ($type === 'offer')
        <a class="btn btn-primary btn-sm" href="/offers/offer">{{ __('offers.offers') }} <span class="badge badge-light">{{ $offers->total() }}</span></a>
        <a class="btn btn-light btn-sm" href="/offers/issue">{{ __('offers.problems') }} <span class="badge badge-light">{{ $otherCount }}</span></a>
    @else
        <a class="btn btn-light btn-sm" href="/offers/offer">{{ __('offers.offers') }} <span class="badge badge-light">{{ $otherCount }}</span></a>
        <a class="btn btn-primary btn-sm" href="/offers/issue">{{ __('offers.problems') }} <span class="badge badge-light">{{ $offers->total() }}</span></a>
    @endif

    @if ($offers->isNotEmpty())
        <br>{{ __('main.sort') }}:
        <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
        <a href="/offers/{{ $type }}?sort=rating" class="badge badge-{{ $active }}">{{ __('main.votes') }}</a>

        <?php $active = ($order === 'created_at') ? 'success' : 'light'; ?>
        <a href="/offers/{{ $type }}?sort=time" class="badge badge-{{ $active }}">{{ __('main.date') }}</a>

        <?php $active = ($order === 'status') ? 'success' : 'light'; ?>
        <a href="/offers/{{ $type }}?sort=status" class="badge badge-{{ $active }}">{{ __('main.status') }}</a>

        <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
        <a href="/offers/{{ $type }}?sort=comments" class="badge badge-{{ $active }}">{{ __('main.comments') }}</a>
        <hr>

        @foreach ($offers as $data)
            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/offers/{{ $data->id }}">{{ $data->title }}</a></b> ({{ __('main.votes') }}: {{ $data->rating }})<br>
                {!! $data->getStatus() !!}
            </div>

            <div>{!! bbCode($data->text) !!}<br>
            {{ __('main.added') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})<br>
            <a href="/offers/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
            <a href="/offers/end/{{ $data->id }}">&raquo;</a></div>
        @endforeach

        <br>{{ __('main.total') }}: <b>{{ $offers->total() }}</b><br>
    @else
        {!! showError(__('main.empty_records')) !!}
    @endif

    {{ $offers->links() }}
@stop
