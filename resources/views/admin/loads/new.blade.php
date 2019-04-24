@extends('layout')

@section('title')
    {{ trans('loads.new_publications') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ trans('loads.new_publications') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/loads">{{ trans('loads.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('loads.new_publications') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($downs->isNotEmpty())
        @foreach ($downs as $data)
            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/downs/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})


                <div class="float-right">
                    <a href="/admin/downs/edit/{{ $data->id }}" title="{{ trans('main.edit') }}"><i class="fa fa-pencil-alt"></i></a>

                    @if (isAdmin('boss'))
                        <a href="/admin/downs/delete/{{ $data->id }}?token={{ $_SESSION['token'] }}"  title="{{ trans('main.delete') }}" onclick="return confirm('{{ trans('loads.confirm_delete_down') }}')"><i class="fa fa-times"></i></a>
                    @endif
                </div>
            </div>

            <div>
                {{ trans('loads.load') }}: <a href="/admin/loads/{{ $data->category->id }}">{{ $data->category->name }}</a><br>
                {{ trans('loads.files_images') }}: {{ $data->getFiles()->count() }}/{{ $data->getImages()->count() }}<br>
                {{ trans('main.author') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('loads.empty_downs')) !!}
    @endif
@stop
