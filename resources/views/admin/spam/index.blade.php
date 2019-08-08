@extends('layout')

@section('title')
    {{ trans('index.complains') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.complains') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <?php $active = ($type === 'post') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=post" class="badge badge-{{ $active }}">{{ trans('index.forums') }} {{ $total['post'] }}</a>
    <?php $active = ($type === 'guest') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=guest" class="badge badge-{{ $active }}">{{ trans('index.guestbooks') }} {{ $total['guest'] }}</a>
    <?php $active = ($type === 'message') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=message" class="badge badge-{{ $active }}">{{ trans('index.messages') }} {{ $total['message'] }}</a>
    <?php $active = ($type === 'wall') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=wall" class="badge badge-{{ $active }}">{{ trans('index.wall_posts') }} {{ $total['wall'] }}</a>
    <?php $active = ($type === 'comment') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=comment" class="badge badge-{{ $active }}">{{ trans('main.comments') }} {{ $total['comment'] }}</a>
    <br><br>

    @if ($records->isNotEmpty())
        @foreach ($records as $data)
            <div class="post">
                @if ($data->relate)
                    <div class="b">
                        <i class="fa fa-file"></i>
                        <b>{!! $data->relate->author ? $data->relate->author->getProfile() : $data->relate->user->getProfile() !!}</b>
                        <small>({{ dateFixed($data->relate->created_at, 'd.m.y / H:i:s') }})</small>

                        <div class="float-right">
                            @if (isAdmin())
                                <a href="#" onclick="return deleteSpam(this)" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ trans('main.delete') }}"><i class="fa fa-times"></i></a>
                            @endif
                        </div>
                    </div>
                    <div>{!! bbCode($data->relate->text) !!}</div>
                @else
                    <div class="b">
                        <i class="fa fa-file"></i> <b>{{ trans('admin.spam.message_not_found') }}</b>

                        <div class="float-right">
                            @if (isAdmin())
                                <a href="#" onclick="return deleteSpam(this)" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ trans('main.delete') }}"><i class="fa fa-times"></i></a>
                            @endif
                        </div>
                    </div>
                @endif

                <div>
                    @if ($data->path)
                        <a href="{{ $data->path }}">{{ trans('admin.spam.go_to_message') }}</a><br>
                    @endif
                    {{ trans('main.sent') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('admin.spam.empty_spam')) !!}
    @endif
@stop
