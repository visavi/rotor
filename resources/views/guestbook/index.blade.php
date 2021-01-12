@extends('layout')

@section('title', __('index.guestbook') . ' (' . __('main.page_num', ['page' => $posts->currentPage()]) . ')')

@section('header')
    @if (getUser() || setting('bookadds'))
        <div class="float-right">
            <a class="btn btn-success" href="#" onclick="return postJump()">{{ __('main.write') }}</a>

            @if (isAdmin())
                <a class="btn btn-light" href="/admin/guestbook?page={{ $posts->currentPage() }}"><i class="fas fa-wrench"></i></a>
            @endif
        </div>
    @endif

    <h1>{{ __('index.guestbook') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.guestbook') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($posts->isNotEmpty())
        @foreach ($posts as $post)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    @if ($post->user_id)
                        {!! $post->user->getAvatar() !!}
                        {!! $post->user->getOnline() !!}
                    @else
                        {!! $post->user->getAvatarGuest() !!}
                    @endif
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        @if ($post->user_id)
                            {!! $post->user->getProfile() !!}
                            <small class="section-date text-muted font-italic">{{ dateFixed($post->created_at) }}</small><br>
                            <small class="font-italic">{!! $post->user->getStatus() !!}</small>
                        @else
                            @if ($post->guest_name)
                                <span class="section-author font-weight-bold" data-login="{{ $post->guest_name }}">{{ $post->guest_name }}</span>
                            @else
                                <span class="section-author font-weight-bold" data-login="{{ setting('guestsuser') }}">{{ setting('guestsuser') }}</span>
                            @endif
                            <small class="section-date text-muted font-italic">{{ dateFixed($post->created_at) }}</small><br>
                            <small class="font-italic">{{ setting('guestsuser') }}</small>
                        @endif
                    </div>

                    @if (getUser() && getUser('id') !== $post->user_id)
                        <div class="text-right">
                            <a href="#" onclick="return postReply(this)" data-toggle="tooltip" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>
                            <a href="#" onclick="return postQuote(this)" data-toggle="tooltip" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ $post->getMorphClass() }}" data-id="{{ $post->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $posts->currentPage() }}" rel="nofollow" data-toggle="tooltip" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                        </div>
                    @endif

                    @if ($post->created_at + 600 > SITETIME && getUser() && getUser('id') === $post->user_id)
                        <div class="text-right">
                            <a href="/guestbook/edit/{{ $post->id }}" data-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        </div>
                    @endif
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {!! bbCode($post->text) !!}
                    </div>

                    @if ($post->edit_user_id)
                        <div class="small"><i class="fa fa-exclamation-circle text-danger"></i> {{ __('main.changed') }}: {{ $post->editUser->getName() }} ({{ dateFixed($post->updated_at) }})</div>
                    @endif

                    @if ($post->reply)
                        <div class="text-danger">{{ __('guestbook.answer') }}: {!! bbCode($post->reply) !!}</div>
                    @endif

                    @if (isAdmin())
                        <div class="small text-muted font-italic mt-2">
                            {{ $post->brow }}, {{ $post->ip }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('main.empty_messages')) !!}
    @endif

    {{ $posts->links() }}

    @if (getUser())
        <div class="section-form mb-3 shadow">
            <form action="/guestbook/add" method="post">
                @csrf
                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ __('main.message') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('guesttextlength') }}" id="msg" rows="5" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                    <span class="js-textarea-counter"></span>
                </div>

                <button class="btn btn-primary">{{ __('main.write') }}</button>
            </form>
        </div>

    @elseif (setting('bookadds'))
        <div class="section-form mb-3 shadow">
            <form action="/guestbook/add" method="post">
                @csrf
                <div class="form-group{{ hasError('guest_name') }}">
                    <label for="inputName">{{ __('users.name') }}:</label>
                    <input class="form-control" id="inputName" name="guest_name" maxlength="20" value="{{ getInput('guest_name') }}">
                    <div class="invalid-feedback">{{ textError('guest_name') }}</div>
                </div>

                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ __('main.message') }}:</label>
                    <textarea class="form-control" id="msg" rows="5" maxlength="{{ setting('guesttextlength') }}" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                </div>

                {!! view('app/_captcha') !!}
                <button class="btn btn-primary">{{ __('main.write') }}</button>
            </form>
        </div>
    @else
        {!! showError(__('main.not_authorized')) !!}
    @endif

    <a href="/stickers">{{ __('main.stickers') }}</a>  /
    <a href="/tags">{{ __('main.tags') }}</a>  /
    <a href="/rules">{{ __('main.rules') }}</a>
@stop
