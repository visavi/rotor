@extends('layout')

@section('title')
    {{ __('index.complains') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.complains') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <?php $active = ($type === 'post') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=post" class="badge badge-{{ $active }}">{{ __('index.forums') }} {{ $total['post'] }}</a>
    <?php $active = ($type === 'guest') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=guest" class="badge badge-{{ $active }}">{{ __('index.guestbooks') }} {{ $total['guest'] }}</a>
    <?php $active = ($type === 'message') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=message" class="badge badge-{{ $active }}">{{ __('index.messages') }} {{ $total['message'] }}</a>
    <?php $active = ($type === 'wall') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=wall" class="badge badge-{{ $active }}">{{ __('index.wall_posts') }} {{ $total['wall'] }}</a>
    <?php $active = ($type === 'comment') ? 'success' : 'light'; ?>
    <a href="/admin/spam?type=comment" class="badge badge-{{ $active }}">{{ __('main.comments') }} {{ $total['comment'] }}</a>
    <br><br>

    @if ($records->isNotEmpty())
        @foreach ($records as $data)
            <div class="post">
                @if ($data->relate)
                    <div class="b">
                        @if ($data->relate->user_id || $data->relate->author_id)
                            <?php $user = $data->relate->author ?? $data->relate->user; ?>
                            <div class="img">
                                {!! $user->getAvatar() !!}
                                {!! $user->getOnline() !!}
                            </div>
                            <b>{!! $user->getProfile() !!}</b>
                        @else
                            <div class="img">
                                <img class="avatar" src="/assets/img/images/avatar_guest.png" alt="">
                            </div>
                            <b>{{ setting('guestsuser') }}</b>
                        @endif

                        <small>({{ dateFixed($data->relate->created_at, 'd.m.y / H:i:s') }})</small>

                        <div class="float-right">
                            @if (isAdmin())
                                <a href="#" onclick="return deleteSpam(this)" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times"></i></a>
                            @endif
                        </div>
                    </div>
                    <div>{!! bbCode($data->relate->text) !!}</div>
                @else
                    <div class="b">
                        <i class="fa fa-file"></i> <b>{{ __('admin.spam.message_not_found') }}</b>

                        <div class="float-right">
                            @if (isAdmin())
                                <a href="#" onclick="return deleteSpam(this)" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times"></i></a>
                            @endif
                        </div>
                    </div>
                @endif

                <div>
                    @if ($data->path)
                        <a href="{{ $data->path }}">{{ __('admin.spam.go_to_message') }}</a><br>
                    @endif
                    {{ __('main.sent') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('admin.spam.empty_spam')) !!}
    @endif

    {{ $records->links('app/_paginator') }}
@stop
