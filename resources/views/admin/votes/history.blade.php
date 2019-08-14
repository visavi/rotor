@extends('layout')

@section('title')
    {{ trans('votes.archive_votes') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/votes">{{ trans('index.votes') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('votes.archive_votes') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($votes->isNotEmpty())
        @foreach ($votes as $vote)
            <div class="b">
                <i class="fa fa-chart-bar"></i>
                <b><a href="/votes/history/{{ $vote['id'] }}">{{ $vote->title }}</a></b>

                <div class="float-right">
                    <a href="/admin/votes/edit/{{ $vote->id }}" data-toggle="tooltip" title="{{ trans('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/votes/close/{{ $vote->id }}?token={{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ trans('main.open') }}"><i class="fa fa-unlock text-muted"></i></a>

                    @if (isAdmin('boss'))
                        <a href="/admin/votes/delete/{{ $vote->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('votes.confirm_delete') }}')" data-toggle="tooltip" title="{{ trans('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                </div>

                @endif
            </div>
            <div>
                @if ($vote->topic->id)
                    {{ trans('forums.topic') }}: <a href="/topics/{{ $vote->topic->id }}">{{ $vote->topic->title }}</a><br>
                @endif

                {{ trans('main.created') }}: {{ dateFixed($vote->created_at) }}<br>
                {{ trans('main.votes') }}: {{ $vote->count }}<br>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('votes.empty_votes')) !!}
    @endif
@stop
