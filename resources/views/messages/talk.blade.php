@extends('layout')

@section('title', __('messages.dialogue_with', ['user' => $user->getName()]))

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
            <?php $author = $data->type === $data::IN ? $data->author : $data->user; ?>
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {{ $author->getAvatar() }}
                    {{ $author->getOnline() }}
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        {{ $author->getProfile() }}

                        @unless ($data->reading)
                            <span class="badge bg-info">{{ __('messages.new') }}</span>
                        @endunless
                    </div>

                    <div class="section-date text-muted fst-italic small">
                        {{ dateFixed($data->created_at) }}

                        @if ($data->type === $data::IN)
                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ $data->getMorphClass() }}" data-id="{{ $data->id }}" data-token="{{ csrf_token() }}" rel="nofollow" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                        @else
                            <i class="fas {{ $data->recipient_read === 0 ? 'fa-check' : 'fa-check-double' }} text-success"></i>
                        @endif
                    </div>
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {{ bbCode($data->text) }}
                    </div>

                    @include('app/_media_viewer', ['model' => $data])
                </div>
            </div>
        @endforeach

        {{ $messages->links() }}
    @else
        {{ showError(__('messages.empty_dialogue')) }}
    @endif

    @if ($user->exists)
        <div class="section-form mb-3 shadow">
            <form action="/messages/send?user={{ $user->login }}" method="post">
                @csrf
                <div class="mb-3{{ hasError('msg') }}">
                    <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                    <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" id="msg" rows="5" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                    <span class="js-textarea-counter"></span>
                </div>

                @include('app/_upload_file', ['files' => $files, 'type' => App\Models\Message::$morphName])

                @if (getUser('point') < setting('privatprotect'))
                    {{ getCaptcha() }}
                @endif

                <button class="btn btn-primary">{{ __('main.write') }}</button>
            </form>
        </div>
    @endif

    <div class="mb-3">
        {{ __('main.total') }}: <b>{{ $messages->total() }}</b>
    </div>

    @if ($messages->isNotEmpty())
        <i class="fa fa-times"></i> <a href="/messages/delete/{{ $user->id }}?_token={{ csrf_token() }}" onclick="return confirm('{{ __('messages.delete_confirm') }}')">{{ __('messages.delete_talk') }}</a><br>
    @endif

    <i class="fa fa-search"></i> <a href="/searchusers">{{ __('index.user_search') }}</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">{{ __('index.contacts') }}</a> / <a href="/ignores">{{ __('index.ignores') }}</a><br>
@stop
