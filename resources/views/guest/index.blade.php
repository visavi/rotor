@extends('layout')

@section('title')
    {{ trans('guest.title', ['page' => $page->current]) }}
@stop

@section('content')

    <h1>{{ trans('guest.header') }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('guest.header') }}</li>
        </ol>
    </nav>

    <a href="/rules">{{ trans('common.rules') }}</a> /
    <a href="/smiles">{{ trans('common.smiles') }}</a> /
    <a href="/tags">{{ trans('common.tags') }}</a>

    @if (isAdmin())
        / <a href="/admin/book?page={{ $page->current }}">{{ trans('common.management') }}</a>
    @endif
    <hr>

    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)

            <div class="post">
                <div class="b">

                    @if (getUser() && getUser('id') != $data->user_id)
                        <div class="float-right">
                            <a href="#" onclick="return postReply(this)" data-toggle="tooltip" title="{{ trans('common.reply') }}"><i class="fa fa-reply text-muted"></i></a>
                            <a href="#" onclick="return postQuote(this)" data-toggle="tooltip" title="{{ trans('common.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Guest::class }}" data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page->current }}" rel="nofollow" data-toggle="tooltip" title="{{ trans('common.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                        </div>
                    @endif

                    @if (getUser() && getUser('id') == $data->user_id && $data->created_at + 600 > SITETIME)
                        <div class="float-right">
                            <a href="/book/edit/{{ $data->id }}" data-toggle="tooltip" title="{{ trans('common.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        </div>
                    @endif

                    <div class="img">{!! userAvatar($data->user) !!}</div>

                    @if ($data->user_id)
                        <b>{!! profile($data->user) !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                        {!! userStatus($data->user) !!} {!! userOnline($data->user) !!}
                    @else
                        <b>{{ setting('guestsuser') }}</b> <small>({{ dateFixed($data->created_at) }})</small>
                    @endif
                </div>

                <div class="message">{!! bbCode($data->text) !!}</div>

                @if ($data->edit_user_id)
                    <small><i class="fa fa-exclamation-circle text-danger"></i> {{ trans('guest.edited') }}: {{ $data->editUser->login }} ({{ dateFixed($data->updated_at) }})</small><br>
                @endif

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif

                @if ($data->reply))
                    <br><span style="color:#ff0000">{{ trans('guest.answer') }}: {!! bbCode($data->reply) !!}</span>
                @endif
            </div>
        @endforeach

        {!! pagination($page) !!}

    @else
        {!! showError(trans('guest.empty_messages')) !!}
    @endif

    @if (getUser())
        <div class="form">
            <form action="/book/add" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ trans('guest.message') }}:</label>
                    <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ trans('guest.message_text') }}" required>{{ getInput('msg') }}</textarea>
                    {!! textError('msg') !!}
                </div>

                <button class="btn btn-primary">{{ trans('guest.write') }}</button>
            </form>
        </div><br>

    @elseif (setting('bookadds') == 1)

        <div class="form">
            <form action="/book/add" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ trans('guest.message') }}:</label>
                    <textarea class="form-control" id="msg" rows="5" name="msg" placeholder="{{ trans('guest.message_text') }}" required>{{ getInput('msg') }}</textarea>
                    {!! textError('msg') !!}
                </div>

                {!! view('app/_captcha') !!}

                <button class="btn btn-primary">{{ trans('guest.write') }}</button>
            </form>
        </div><br>

    @else
        {!! showError(trans('guest.not_authorized')) !!}
    @endif
@stop
