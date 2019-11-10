@extends('layout')

@section('title')
    {{ __('index.search_results') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/searchusers">{{ __('index.search_users') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.search_results') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isNotEmpty())
        <div class="mb-3">
            @foreach($users as $user)
                <div  class="text-truncate bg-light my-1">
                    <div class="img">
                        {!! $user->getAvatar() !!}
                        {!! $user->getOnline() !!}
                    </div>

                    <b><a href="/users/{{ $user->login }}">{{ $user->login }}</a></b>
                    ({{ plural($user->point, setting('scorename')) }})
                </div>
            @endforeach
        </div>

        {{ __('main.total_found') }}: <b>{{ $users->total() }}</b><br>
    @else
        {!! showError(__('main.empty_found')) !!}
    @endif

    {{ $users->links('app/_paginator') }}
@stop
