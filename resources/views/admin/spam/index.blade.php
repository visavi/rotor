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
            <div class="section mb-3 shadow">
                @if ($data->relate)
                    @if ($data->relate->user_id || $data->relate->author_id)
                        <?php $user = $data->relate->author ?? $data->relate->user; ?>

                        <div class="user-avatar">
                            {!! $user->getAvatar() !!}
                            {!! $user->getOnline() !!}
                        </div>

                        <b>{!! $user->getProfile() !!}</b>
                    @else
                        <div class="user-avatar">
                            <img class="img-fluid rounded-circle avatar-default" src="/assets/img/images/avatar_guest.png" alt="">
                        </div>
                        <b>{{ setting('guestsuser') }}</b>
                    @endif

                    <div class="section-user d-flex align-items-center">
                        <div class="flex-grow-1">


                            <small>({{ dateFixed($data->relate->created_at, 'd.m.Y / H:i:s') }})</small>
                        </div>

                        <div class="text-right">
                            @if (isAdmin())
                                <a href="#" onclick="return deleteSpam(this)" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times"></i></a>
                            @endif
                        </div>
                    </div>

                    <div class="section-body border-top my-1 py-1">
                        <div class="section-message">
                            {!! bbCode($data->relate->text) !!}
                        </div>
                    </div>
                @else
                    <div class="b">
                        <i class="fa fa-file"></i> <b>{{ __('main.message_not_found') }}</b>

                        <div class="text-right">
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

    {{ $records->links() }}
@stop
