@extends('layout')

@section('title')
    {{ __('messages.dialogue_with', ['user' => $user->getName()]) }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item"><a href="/messages">{{ __('index.messages') }}</a></li>
            <li class="breadcrumb-item active">{{ __('messages.dialogue') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser()->isIgnore($user))
        <div class="p-1 my-1 bg-danger text-white">
            <i class="fas fa-exclamation-triangle"></i>
            {{ __('messages.warning') }}
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

                        @if ($data->type === 'out')
                            <i class="fas {{ $data->recipient_read ? 'fa-check-double' : 'fa-check' }} text-success"></i>
                        @endif

                        @if ($data->type === 'in')
                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Message::class }} " data-id="{{ $data->id }}" data-token="{{ $_SESSION['token'] }}" rel="nofollow" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                        @endif
                    </div>

                    <b>{!! $author->getProfile() !!}</b>

                    @unless ($data->reading)
                        <br><span class="badge badge-info">{{ __('messages.new') }}</span>
                    @endunless
                </div>
                <div class="message">


                    {!! bbCode($data->text) !!}
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(__('messages.empty_dialogue')) !!}
    @endif

    @if ($user->exists)
        <br>
        <div class="form">
            <form action="/messages/send?user={{ $user->login }}" method="post">
                @csrf
                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">{{ __('main.message') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                    <span class="js-textarea-counter"></span>
                </div>

                @if (getUser('point') < setting('privatprotect'))
                    {!! view('app/_captcha') !!}
                @endif

                <button class="btn btn-primary">{{ __('main.write') }}</button>
            </form>
        </div><br>
    @endif

    {{ __('main.total') }}: <b>{{ $page->total }}</b><br><br>

    @if ($page->total)
        <i class="fa fa-times"></i> <a href="/messages/delete/{{ $user->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('messages.delete_confirm') }}')">{{ __('messages.delete_talk') }}</a><br>
    @endif

    <i class="fa fa-search"></i> <a href="/searchusers">{{ __('index.user_search') }}</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">{{ __('index.contacts') }}</a> / <a href="/ignores">{{ __('index.ignores') }}</a><br>
@stop
