@extends('layout')

@section('title')
    {{ __('index.guestbooks') }} ({{ __('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ __('index.guestbooks') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.guestbooks') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/rules">{{ __('main.rules') }}</a> /
    <a href="/stickers">{{ __('main.stickers') }}</a> /
    <a href="/tags">{{ __('main.tags') }}</a>

    @if (isAdmin())
        / <a href="/admin/guestbooks?page={{ $page->current }}">{{ __('main.management') }}</a>
    @endif
    <hr>

    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)
            <div class="post">
                <div class="b">
                    @if (getUser() && getUser('id') !== $data->user_id)
                        <div class="float-right">
                            <a href="#" onclick="return postReply(this)" data-toggle="tooltip" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>
                            <a href="#" onclick="return postQuote(this)" data-toggle="tooltip" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Guestbook::class }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page->current }}" rel="nofollow" data-toggle="tooltip" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                        </div>
                    @endif

                    @if ($data->created_at + 600 > SITETIME && getUser() && getUser('id') === $data->user_id)
                        <div class="float-right">
                            <a href="/guestbooks/edit/{{ $data->id }}" data-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        </div>
                    @endif

                    <div class="img">
                        {!! $data->user->getAvatar() !!}

                        @if ($data->user_id)
                            {!! $data->user->getOnline() !!}
                        @endif
                    </div>

                    @if($data->user_id === 0)
                        <b class="author" data-login="{{ setting('guestsuser') }}">{{ setting('guestsuser') }}</b> <small>({{ dateFixed($data->created_at) }})</small>
                    @else
                        <b>{!! $data->user->getProfile() !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                        {!! $data->user->getStatus() !!}
                    @endif
                </div>

                <div class="message">{!! bbCode($data->text) !!}</div>

                @if ($data->edit_user_id)
                    <small><i class="fa fa-exclamation-circle text-danger"></i> {{ __('main.changed') }}: {{ $data->editUser->getName() }} ({{ dateFixed($data->updated_at) }})</small><br>
                @endif

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif

                @if ($data->reply)
                    <br><span style="color:#ff0000">{{ __('guestbooks.answer') }}: {!! bbCode($data->reply) !!}</span>
                @endif
            </div>
        @endforeach

        {!! pagination($page) !!}

    @else
        {!! showError(__('main.empty_messages')) !!}
    @endif

    @if (getUser())
        <div class="form">
            <form action="/guestbooks/add" method="post">
                @csrf
                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ __('main.message') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('guesttextlength') }}" id="msg" rows="5" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                    <span class="js-textarea-counter"></span>
                </div>

                <button class="btn btn-primary">{{ __('main.write') }}</button>
            </form>
        </div><br>

    @elseif (setting('bookadds'))

        <div class="form">
            <form action="/guestbooks/add" method="post">
                @csrf
                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ __('main.message') }}:</label>
                    <textarea class="form-control" id="msg" rows="5" maxlength="{{ setting('guesttextlength') }}" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                </div>

                {!! view('app/_captcha') !!}
                <button class="btn btn-primary">{{ __('main.write') }}</button>
            </form>
        </div><br>

    @else
        {!! showError(__('main.not_authorized')) !!}
    @endif
@stop
