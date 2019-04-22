@extends('layout')

@section('title')
    {{ trans('index.search_results') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/searchusers">{{ trans('index.search_users') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.search_results') }}</li>
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

                    <b><a href="/admin/users/edit?user={{ $user->login }}">{{ $user->login }}</a></b>
                    ({{ plural($user->point, setting('scorename')) }})
                </div>
            @endforeach
        </div>

        @if (isset($page))
            {!! pagination($page) !!}
            {{ trans('users.total_found') }}: <b>{{ $page->total }}</b><br>
        @else
            {{ trans('users.total_found') }}: <b>{{ $users->count() }}</b><br>
        @endif
    @else
        {!! showError(trans('users.empty_found')) !!}
    @endif
@stop
