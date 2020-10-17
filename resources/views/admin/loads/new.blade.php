@extends('layout')

@section('title', __('loads.new_publications') . ' (' . __('main.page_num', ['page' => $downs->currentPage()]) . ')')

@section('header')
    <h1>{{ __('loads.new_publications') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/loads">{{ __('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.new_publications') }}</li>
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
                    <a href="/admin/downs/edit/{{ $data->id }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt"></i></a>

                    @if (isAdmin('boss'))
                        <a href="/admin/downs/delete/{{ $data->id }}?token={{ $_SESSION['token'] }}"  title="{{ __('main.delete') }}" onclick="return confirm('{{ __('loads.confirm_delete_down') }}')"><i class="fa fa-times"></i></a>
                    @endif
                </div>
            </div>

            <div>
                {{ __('loads.load') }}: <a href="/admin/loads/{{ $data->category->id }}">{{ $data->category->name }}</a><br>
                {{ __('loads.files_images') }}: {{ $data->getFiles()->count() }}/{{ $data->getImages()->count() }}<br>
                {{ __('main.author') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})
            </div>
        @endforeach
    @else
        {!! showError(__('loads.empty_downs')) !!}
    @endif

    {{ $downs->links() }}
@stop
