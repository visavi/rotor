@extends('layout')

@section('title', __('index.messages'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item">{{ __('index.messages') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="text-end mb-2">
        <a class="d-inline-flex align-items-center gap-1" href="/users"><i class="fa fa-search"></i> {{ __('index.user_search') }}</a>
    </div>

    @if ($messages->isNotEmpty())
        <div class="section mb-3 shadow dialogue-list">
            @foreach ($messages as $data)
                <?php $login = $data->author->exists ? $data->author->login : $data->author_id ?>
                <div class="dialogue-row" data-href="/messages/talk/{{ $login }}">
                    <div class="dialogue-avatar">
                        @if ($data->author_id)
                            {{ $data->author->getAvatar() }}
                            {{ $data->author->getOnline() }}
                        @else
                            <span class="avatar-default avatar-guest rounded-circle"><i class="fas fa-headset"></i></span>
                        @endif
                    </div>

                    <div class="dialogue-main">
                        <div class="dialogue-head">
                            {{-- Обёртка держит имя и значок роли одним флекс-элементом, иначе space-between уносит значок в центр --}}
                            <span>
                                @if ($data->author_id)
                                    {{ $data->author->getProfile() }}
                                @else
                                    <b>{{ __('messages.system') }}</b>
                                @endif
                            </span>

                            <span class="dialogue-date text-muted fst-italic">
                                {{ dateFixed($data->created_at) }}

                                @if ($data->type === $data::OUT)
                                    <i class="fas {{ $data->recipient_read === 0 ? 'fa-check' : 'fa-check-double' }} text-success"></i>
                                @endif
                            </span>
                        </div>

                        <div class="dialogue-preview text-muted">
                            {{ $data->getText() }}

                            @unless ($data->all_reading)
                                <span class="badge bg-info">{{ __('messages.new') }}</span>
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
                <i class="far fa-envelope fa-2x mb-2"></i>
                {{ __('main.empty_messages') }}
            </div>
        </div>
    @endif
@stop
