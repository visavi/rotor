@extends('layout')

@section('title', $category->name)

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/admin/stickers/sticker/create?cid={{ $category->id ?? 0 }}">{{ __('stickers.upload') }}</a>
    </div>

    <h1>{{ $category->name }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers">{{ __('index.stickers') }}</a></li>
            <li class="breadcrumb-item active">{{ $category->name }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($stickers->isNotEmpty())
        @foreach ($stickers as $sticker)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <img src="{{ $sticker->name }}" alt="">
                    {{ $sticker->code }}

                    <div class="float-right">
                        <a href="/admin/stickers/sticker/edit/{{ $sticker->id }}?page={{ $stickers->currentPage() }}" data-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt"></i></a>

                        <a href="/admin/stickers/sticker/delete/{{ $sticker->id }}?page={{ $stickers->currentPage() }}&amp;token={{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}" onclick="return confirm('{{ __('stickers.confirm_delete_sticker') }}')"><i class="fa fa-times"></i></a>
                    </div>
                </div>
            </div>
        @endforeach

        {{ $stickers->links() }}

        {{ __('stickers.total_stickers') }}: <b>{{ $stickers->total() }}</b><br>
    @else
        {!! showError(__('stickers.empty_stickers')) !!}
    @endif
@stop
