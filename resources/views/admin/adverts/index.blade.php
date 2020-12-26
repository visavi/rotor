@extends('layout')

@section('title', __('index.advertising'))

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/adverts/create">{{ __('adverts.create_advert') }}</a>
    </div>

    <h1>{{ __('index.advertising') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.advertising') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($records->isNotEmpty())
        <form action="/admin/adverts/delete?page={{ $records->currentPage() }}" method="post">
            @csrf
            @foreach ($records as $data)
                <div class="b">
                    <i class="fa fa-check-circle"></i>
                    <b><a href="{{ $data->site }}">{{ $data->name }}</a></b> ({!! $data->user->getProfile() !!})

                    <div class="float-right">
                        <a href="/admin/adverts/edit/{{ $data->id }}?page={{ $records->currentPage() }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>
                </div>

                {{ __('adverts.expires') }}: {{ dateFixed($data->deleted_at) }}<br>
                {{ __('adverts.color') }}: {!! $data->color ? '<span style="color:' . $data->color .'">'. $data->color .'</span>' : '<i class="fas fa-times text-danger"></i>' !!},
                {{ __('adverts.bold') }}: {!! $data->bold ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}<br>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
            </div>
        </form>

        <br>{{ __('adverts.total_links') }}: <b>{{ $records->total() }}</b><br>
    @else
        {!! showError(__('adverts.empty_links')) !!}
    @endif

    {{ $records->links() }}
@stop
