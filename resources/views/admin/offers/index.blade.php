@extends('layout')

@section('title')
    {{ trans('offers.title') }}
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/offers/create?type={{ $type }}">{{ trans('offers.add_offer') }}</a><br>
        </div><br>
    @endif

    <h1>{{ trans('offers.title') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('offers.title') }}</li>
            <li class="breadcrumb-item"><a href="/offers/{{ $type }}?page={{ $page->current }}">{{ trans('main.review') }}</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($type === 'offer')
        <a class="btn btn-primary btn-sm" href="/admin/offers/offer">{{ trans('offers.offers') }} <span class="badge badge-light">{{ $page->total }}</span></a>
        <a class="btn btn-light btn-sm" href="/admin/offers/issue">{{ trans('offers.problems') }} <span class="badge badge-light">{{ $page->otherTotal }}</span></a>
    @else
        <a class="btn btn-light btn-sm" href="/admin/offers/offer">{{ trans('offers.offers') }} <span class="badge badge-light">{{ $page->otherTotal }}</span></a>
        <a class="btn btn-primary btn-sm" href="/admin/offers/issue">{{ trans('offers.problems') }} <span class="badge badge-light">{{ $page->total }}</span></a>
    @endif

    @if ($offers->isNotEmpty())
        <br>{{ trans('main.sort') }}:
        <?php $active = ($order === 'rating') ? 'success' : 'light'; ?>
        <a href="/admin/offers/{{ $type }}?sort=rating" class="badge badge-{{ $active }}">{{ trans('main.votes') }}</a>

        <?php $active = ($order === 'created_at') ? 'success' : 'light'; ?>
        <a href="/admin/offers/{{ $type }}?sort=time" class="badge badge-{{ $active }}">{{ trans('main.date') }}</a>

        <?php $active = ($order === 'status') ? 'success' : 'light'; ?>
        <a href="/admin/offers/{{ $type }}?sort=status" class="badge badge-{{ $active }}">{{ trans('main.status') }}</a>

        <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
        <a href="/admin/offers/{{ $type }}?sort=comments" class="badge badge-{{ $active }}">{{ trans('main.comments') }}</a>
        <hr>

        <form action="/admin/offers/delete?type={{ $type }}&amp;page={{ $page->current }}" method="post">
            @csrf
            @foreach ($offers as $data)
                <div class="b">
                    <div class="float-right">
                        <a href="/admin/offers/reply/{{ $data->id }}" data-toggle="tooltip" title="{{ trans('main.reply') }}"><i class="fas fa-reply text-muted"></i></a>
                        <a href="/admin/offers/edit/{{ $data->id }}" data-toggle="tooltip" title="{{ trans('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>

                    <i class="fa fa-file"></i>
                    <b><a href="/admin/offers/{{ $data->id }}">{{ $data->title }}</a></b> ({{ trans('main.votes') }}: {{ $data->rating }})<br>
                    {!! $data->getStatus() !!}
                </div>

                <div>{!! bbCode($data->text) !!}<br>
                    {{ trans('main.added') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})<br>
                    <a href="/offers/comments/{{ $data->id }}">{{ trans('main.comments') }}</a> ({{ $data->count_comments }})
                    <a href="/offers/end/{{ $data['id'] }}">&raquo;</a></div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">{{ trans('main.delete_selected') }}</button>
            </div>
        </form>

        {!! pagination($page) !!}

        {{ trans('offers.total') }}: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError(trans('offers.empty_offers')) !!}
    @endif

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/offers/restatement?token={{ $_SESSION['token'] }}">{{ trans('main.recount') }}</a><br>
    @endif
@stop
