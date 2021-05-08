@extends('layout')

@section('title', __('index.offers'))

@section('header')
    <div class="float-end">
        <a class="btn btn-success" href="/offers/create?type={{ $type }}">{{ __('main.add') }}</a>
        <a class="btn btn-light" href="/offers/{{ $type }}?page={{ $offers->currentPage() }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.offers') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.offers') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        @if ($type === 'offer')
            <a class="btn btn-primary btn-sm" href="/admin/offers/offer">{{ __('offers.offers') }} <span class="badge bg-light text-dark">{{ $offers->total() }}</span></a>
            <a class="btn btn-light btn-sm" href="/admin/offers/issue">{{ __('offers.problems') }} <span class="badge bg-light text-dark">{{ $otherCount }}</span></a>
        @else
            <a class="btn btn-light btn-sm" href="/admin/offers/offer">{{ __('offers.offers') }} <span class="badge bg-light text-dark">{{ $otherCount }}</span></a>
            <a class="btn btn-primary btn-sm" href="/admin/offers/issue">{{ __('offers.problems') }} <span class="badge bg-light text-dark">{{ $offers->total() }}</span></a>
        @endif
    </div>

    @if ($offers->isNotEmpty())
        {{ __('main.sort') }}:
        <?php $active = ($order === 'rating') ? 'success' : 'light text-dark'; ?>
        <a href="/admin/offers/{{ $type }}?sort=rating" class="badge bg-{{ $active }}">{{ __('main.votes') }}</a>

        <?php $active = ($order === 'created_at') ? 'success' : 'light text-dark'; ?>
        <a href="/admin/offers/{{ $type }}?sort=time" class="badge bg-{{ $active }}">{{ __('main.date') }}</a>

        <?php $active = ($order === 'status') ? 'success' : 'light text-dark'; ?>
        <a href="/admin/offers/{{ $type }}?sort=status" class="badge bg-{{ $active }}">{{ __('main.status') }}</a>

        <?php $active = ($order === 'count_comments') ? 'success' : 'light text-dark'; ?>
        <a href="/admin/offers/{{ $type }}?sort=comments" class="badge bg-{{ $active }}">{{ __('main.comments') }}</a>
        <hr>

        <form action="/admin/offers/delete?type={{ $type }}&amp;page={{ $offers->currentPage() }}" method="post">
            @csrf
            @foreach ($offers as $data)
                <div class="section mb-3 shadow">
                    <div class="section-title">
                        <i class="fa fa-file"></i>
                        <a href="/admin/offers/{{ $data->id }}">{{ $data->title }}</a> ({{ __('main.votes') }}: {{ $data->rating }})
                        <div class="float-end">
                            <a href="/admin/offers/reply/{{ $data->id }}" data-bs-toggle="tooltip" title="{{ __('main.reply') }}"><i class="fas fa-reply text-muted"></i></a>
                            <a href="/admin/offers/edit/{{ $data->id }}" data-bs-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                            <input type="checkbox" name="del[]" value="{{ $data->id }}">
                        </div>
                    </div>

                    <div class="section-body">
                        {{ $data->getStatus() }}<br>
                        {{ bbCode($data->text) }}<br>
                        {{ __('main.added') }}: {{ $data->user->getProfile() }} ({{ dateFixed($data->created_at) }})<br>
                        <a href="/offers/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                        <a href="/offers/end/{{ $data->id }}">&raquo;</a>
                    </div>
                </div>
            @endforeach

            <div class="float-end">
                <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
            </div>
        </form>

        {{ $offers->links() }}

        {{ __('main.total') }}: <b>{{ $offers->total() }}</b><br>
    @else
        {{ showError(__('main.empty_records')) }}
    @endif

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/offers/restatement?token={{ $_SESSION['token'] }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
