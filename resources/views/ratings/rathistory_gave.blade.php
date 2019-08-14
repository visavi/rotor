@extends('layout')

@section('title')
    {{ trans('ratings.votes_gave') }} {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->login }}</a></li>

            @if (getUser('id') !== $user->id)
                <li class="breadcrumb-item"><a href="/users/{{ $user->login }}/rating">{{ trans('index.reputation_edit') }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ trans('ratings.votes_gave') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/ratings/{{ $user->login }}/received" class="badge badge-light">{{ trans('ratings.votes_received') }}</a>
    <a href="/ratings/{{ $user->login }}/gave" class="badge badge-success">{{ trans('ratings.votes_gave') }}</a>
    <hr>

    @if ($ratings->isNotEmpty())
        @foreach ($ratings as $data)
            <div class="b">
                @if ($data->vote === '-')
                    <i class="fa fa-thumbs-down text-danger"></i>
                @else
                    <i class="fa fa-thumbs-up text-success"></i>
                @endif

                <b>{!! $data->recipient->getProfile() !!}</b> ({{ dateFixed($data->created_at) }})
            </div>
            <div>
                {{ trans('main.comment') }}:
                {!! bbCode($data->text) !!}
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('ratings.empty_ratings')) !!}
    @endif
@stop
