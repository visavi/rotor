@extends('layout')

@section('title', __('messages.dialogue_with', ['user' => $user->getName()]))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/messages">{{ __('index.messages') }}</a></li>
            <li class="breadcrumb-item active">{{ __('messages.dialogue') }}</li>
        </ol>
    </nav>
@stop

@section('content')

    {{-- Действия над собеседником: подарок, перевод и прочее от модулей --}}
    @if ($user->exists)
        <x-section body-class="profile-actions">
            @hook('messageActions', $user)
        </x-section>
    @endif

    @if ($messages->isNotEmpty())
        <div class="mb-3">
            @foreach ($messages as $data)
                @php
                    $incoming = $data->type === $data::IN;
                    $author = $incoming ? $data->author : $data->user;
                @endphp

                {{-- Классы section-author / section-date / section-message нужны цитированию --}}
                <div class="talk-row{{ $incoming ? '' : ' is-own' }}">
                    <div class="talk-avatar">
                        {{ $author->getAvatar() }}
                        {{ $author->getOnline() }}
                    </div>

                    <div class="section talk-bubble">
                        <div class="talk-head">
                            <span class="talk-name">
                                {{ $author->getProfile() }}

                                @unless ($data->reading)
                                    <span class="badge bg-info">{{ __('messages.new') }}</span>
                                @endunless
                            </span>

                            @if ($incoming)
                                <span class="talk-actions">
                                    @if ($user->exists)
                                        <a href="#" onclick="return postQuote(this)" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>
                                    @endif

                                    <a href="#" data-ajax data-ajax-url="/ajax/complaint" data-ajax-confirm="{{ __('main.confirm_complaint') }}" data-ajax-icon="fa fa-check text-muted" data-type="{{ $data->getMorphClass() }}" data-id="{{ $data->id }}" rel="nofollow" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                                </span>
                            @endif
                        </div>

                        <div class="section-message">
                            {{ $data->getText() }}
                        </div>

                        @include('app/_media_viewer', ['model' => $data])

                        <div class="talk-meta">
                            <span class="section-date text-muted fst-italic" data-date="{{ dateFixed($data->created_at, original: true) }}">
                                {{ dateFixed($data->created_at) }}
                            </span>

                            @unless ($incoming)
                                <i class="fas {{ $data->recipient_read === 0 ? 'fa-check' : 'fa-check-double' }} text-success"
                                   title="{{ $data->recipient_read === 0 ? __('messages.sent') : __('messages.read') }}"></i>
                            @endunless
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $messages->links() }}
    @else
        <div class="section mb-3 shadow">
            <div class="section-body d-flex flex-column align-items-center text-muted py-4">
                <i class="far fa-comments fa-2x mb-2"></i>
                {{ __('messages.empty_dialogue') }}
            </div>
        </div>
    @endif

    @if ($user->exists)
        <div class="section-form mb-3 shadow">
            <form action="/messages/send?user={{ $user->login }}" method="post">
                @csrf
                <div class="mb-3{{ hasError('msg') }}">
                    <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                    <textarea class="form-control tiptap" maxlength="{{ setting('comment_text_max') }}" id="msg" rows="5" name="msg" data-relate-type="{{ \App\Models\Message::$morphName }}" data-relate-id="0" placeholder="{{ __('main.message') }}" required>{{ old('msg') }}</textarea>
                    <div class="invalid-feedback">{{ textError('msg') }}</div>
                    <span class="js-textarea-counter"></span>
                </div>

                @include('app/_upload_file', [
                    'model' => App\Models\Message::getModel(),
                    'files' => $files,
                ])

                @if (getUser('point') < setting('privatprotect'))
                    {{ getCaptcha() }}
                @endif

                {{-- Кнопки модулей рядом с отправкой --}}
                @hook('messageFormEnd', $user)

                <button class="btn btn-primary">{{ __('main.write') }}</button>
            </form>
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <span class="text-muted">
            {{ __('main.total') }}: <b>{{ $messages->total() }}</b>
            <a class="ms-3" href="/users"><i class="fa fa-search"></i> {{ __('index.user_search') }}</a>
        </span>

        @if ($messages->isNotEmpty())
            <form action="/messages/delete/{{ $user->id }}" method="post" onsubmit="return confirm('{{ __('messages.delete_confirm') }}')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="fa fa-times"></i> {{ __('messages.delete_talk') }}</button>
            </form>
        @endif
    </div>
@stop

@push('scripts')
    <script type="module">
        updateMessageCount({{ $countMessages }});
    </script>
@endpush
