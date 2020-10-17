@extends('layout')

@section('title', __('index.offers'))

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/offers/create?type={{ $type }}">{{ __('main.add') }}</a><br>
        </div><br>
    @endif

    <h1>{{ __('index.offers') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.offers') }}</li>
            <li class="breadcrumb-item"><a href="/offers/{{ $type }}?page={{ $offers->currentPage() }}">{{ __('main.review') }}</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($type === 'offer')
        <a class="btn btn-primary btn-sm" href="/admin/offers/offer">{{ __('offers.offers') }} <span class="badge badge-light">{{ $offers->total() }}</span></a>
        <a class="btn btn-light btn-sm" href="/admin/offers/issue">{{ __('offers.problems') }} <span class="badge badge-light">{{ $otherCount }}</span></a>
    @else
        <a class="btn btn-light btn-sm" href="/admin/offers/offer">{{ __('offers.offers') }} <span class="badge badge-light">{{ $otherCount }}</span></a>
        <a class="btn btn-primary btn-sm" href="/admin/offers/issue">{{ __('offers.problems') }} <span class="badge badge-light">{{ $offers->total() }}</span></a>
    @endif

    @if ($offers->isNotEmpty())
        <br>{{ __('main.sort') }}:
        <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
        <a href="/admin/offers/{{ $type }}?sort=rating" class="badge badge-{{ $active }}">{{ __('main.votes') }}</a>

        <?php $active = ($order === 'created_at') ? 'success' : 'light'; ?>
        <a href="/admin/offers/{{ $type }}?sort=time" class="badge badge-{{ $active }}">{{ __('main.date') }}</a>

        <?php $active = ($order === 'status') ? 'success' : 'light'; ?>
        <a href="/admin/offers/{{ $type }}?sort=status" class="badge badge-{{ $active }}">{{ __('main.status') }}</a>

        <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
        <a href="/admin/offers/{{ $type }}?sort=comments" class="badge badge-{{ $active }}">{{ __('main.comments') }}</a>
        <hr>

        <form action="/admin/offers/delete?type={{ $type }}&amp;page={{ $offers->currentPage() }}" method="post">
            @csrf
            @foreach ($offers as $data)
                <div class="b">
                    <div class="float-right">
                        <a href="/admin/offers/reply/{{ $data->id }}" data-toggle="tooltip" title="{{ __('main.reply') }}"><i class="fas fa-reply text-muted"></i></a>
                        <a href="/admin/offers/edit/{{ $data->id }}" data-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>

                    <i class="fa fa-file"></i>
                    <b><a href="/admin/offers/{{ $data->id }}">{{ $data->title }}</a></b> ({{ __('main.votes') }}: {{ $data->rating }})<br>
                    {!! $data->getStatus() !!}
                </div>

                <div>{!! bbCode($data->text) !!}<br>
                    {{ __('main.added') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})<br>
                    <a href="/offers/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                    <a href="/offers/end/{{ $data->id }}">&raquo;</a></div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
            </div>
        </form>

        <br>{{ __('main.total') }}: <b>{{ $offers->total() }}</b><br>
    @else
        {!! showError(__('main.empty_records')) !!}
    @endif

    {{ $offers->links() }}

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/offers/restatement?token={{ $_SESSION['token'] }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
