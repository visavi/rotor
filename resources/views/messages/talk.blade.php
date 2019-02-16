@extends('layout')

@section('title')
    {{ trans('messages.dialogue_with', ['user' => $user->getName()]) }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ trans('main.menu') }}</a></li>
            <li class="breadcrumb-item"><a href="/messages">{{ trans('messages.private_messages') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('messages.dialogue') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser()->isIgnore($user))
        <div class="p-1 bg-danger text-white">
            <i class="fas fa-exclamation-triangle"></i>
            {{ trans('messages.warning') }}
        </div>
    @endif

    @if ($messages->isNotEmpty())

        @foreach ($messages as $data)

            <?php $author = $data->type === 'in' ? $data->author : $data->user; ?>
            <div class="post">
                <div class="b">
                    <div class="img">
                        {!! $author->getAvatar() !!}
                        {!! $author->getOnline() !!}
                    </div>

                    <div class="text-muted float-right">
                        {{  dateFixed($data->created_at) }}

                        @if ($data->type === 'in')
                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Message::class }} " data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" rel="nofollow" title="{{ trans('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                        @endif
                    </div>

                    <b>{!! $author->getProfile() !!}</b>

                    @unless ($data->reading)
                        <br><span class="badge badge-info">{{ trans('messages.new') }}</span>
                    @endunless
                </div>
                <div class="message">{!! bbCode($data->text) !!}</div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('messages.empty_dialogue')) !!}
    @endif

    <br>
    <div class="form">
        <form action="/messages/send?user={{ $user->login }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('main.message') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" data-hint="{{ trans('main.characters_left') }}" id="msg" rows="5" name="msg" placeholder="{{ trans('main.message') }}" required>{{ getInput('msg') }}</textarea>
                <span class="js-textarea-counter"></span>
                {!! textError('msg') !!}
            </div>

            @if (getUser('point') < setting('privatprotect'))
                {!! view('app/_captcha') !!}
            @endif

            <button class="btn btn-primary">{{ trans('main.write') }}</button>
        </form>
    </div><br>

    {{ trans('messages.total') }}: <b>{{ $page->total }}</b><br><br>

    @if ($page->total)
        <i class="fa fa-times"></i> <a href="/messages/delete/{{ $user->id }}?token={{ $_SESSION['token'] }}">{{ trans('messages.delete_talk') }}</a><br>
    @endif

    <i class="fa fa-search"></i> <a href="/searchusers">{{ trans('app.user.search') }}</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">{{ trans('app.contact') }}</a> / <a href="/ignores">{{ trans('app.ignore') }}</a><br>
@stop
