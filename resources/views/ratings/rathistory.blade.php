@extends('layout')

@section('title', __('ratings.votes_received') . ' ' . $user->getName())

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ $user->login }}">{{ $user->getName() }}</a></li>

            @if (getUser() && getUser('id') !== $user->id)
                <li class="breadcrumb-item"><a href="/users/{{ $user->login }}/rating">{{ __('index.reputation_edit') }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ __('ratings.votes_received') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <a href="/ratings/{{ $user->login }}/received" class="badge badge-success">{{ __('ratings.votes_received') }}</a>
        <a href="/ratings/{{ $user->login }}/gave" class="badge badge-light">{{ __('ratings.votes_gave') }}</a>
    </div>

    @if ($ratings->isNotEmpty())
        @foreach ($ratings as $data)
            <div class="section-form mb-3 shadow">
                <div class="section-content">
                    @if ($data->vote === '-')
                        <i class="fa fa-thumbs-down text-danger"></i>
                    @else
                        <i class="fa fa-thumbs-up text-success"></i>
                    @endif

                    {!! $data->user->getProfile() !!}
                    <small class="section-date text-muted font-italic">
                        {{ dateFixed($data->created_at) }}
                    </small>

                    <div class="float-right">
                        @if (isAdmin())
                            <a href="#" onclick="return deleteRating(this)" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times"></i></a>
                        @endif
                    </div>
                </div>
                <div class="section-message">
                    {{ __('main.comment') }}:
                    {!! bbCode($data->text) !!}
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('ratings.empty_ratings')) !!}
    @endif

    {{ $ratings->links() }}
@stop
