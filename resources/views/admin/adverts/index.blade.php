@extends('layout')

@section('title')
    {{ trans('adverts.user_advert') }}
@stop

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/adverts/create">{{ trans('adverts.create_advert') }}</a>
    </div><br>

    <h1>{{ trans('adverts.user_advert') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('adverts.user_advert') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($records->isNotEmpty())

        <form action="/admin/adverts/delete?page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($records as $data)
                <div class="b">
                    <i class="fa fa-check-circle"></i>
                    <b><a href="{{ $data->site }}">{{ $data->name }}</a></b> ({!! $data->user->getProfile() !!})

                    <div class="float-right">
                        <a href="/admin/adverts/edit/{{ $data->id }}?page={{ $page->current }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>
                </div>

                {{ trans('adverts.expires') }}: {{ dateFixed($data->deleted_at) }}<br>
                {{ trans('adverts.color') }}: {!! $data->color ? '<span style="color:' . $data->color .'">'. $data->color .'</span>' : '<i class="fas fa-times text-danger"></i>' !!},
                {{ trans('adverts.bold') }}: {!! $data->bold ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}<br>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">{{ trans('main.delete_selected') }}</button>
            </div>
        </form>

        {!! pagination($page) !!}

        {{ trans('adverts.total_links') }}: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError(trans('adverts.empty_links')) !!}
    @endif
@stop
