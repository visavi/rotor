@extends('layout')

@section('title', __('index.messages'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item">{{ __('index.messages') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($messages->isNotEmpty())
        @foreach ($messages as $data)
            <?php $login = $data->author->exists ? $data->author->login : $data->author_id ?>
            <div class="section mb-3 shadow message-block" data-href="/messages/talk/{{ $login }}">

                <div class="user-avatar">
                    @if ($data->author_id)
                        {{ $data->author->getAvatar() }}
                        {{ $data->author->getOnline() }}
                    @else
                        <img class="avatar-default rounded-circle" src="/assets/img/images/avatar_system.png" alt="">
                        <div class="user-status bg-success" title="Онлайн"></div>
                    @endif
                </div>

                <div class="section-user d-flex align-items-start">
                    <div class="flex-grow-1">
                        @if ($data->author_id)
                            {{ $data->author->getProfile() }}
                        @else
                            <b>{{ __('messages.system') }}</b>
                        @endif

                        @unless ($data->all_reading)
                            <span class="badge bg-info">{{ __('messages.new') }}</span>
                        @endunless
                    </div>

                    <div class="section-date text-muted fst-italic small">
                        {{ dateFixed($data->created_at) }}

                        @if ($data->type === $data::OUT)
                            <i class="fas fa-xs {{ $data->recipient_read === 0 ? 'fa-check' : 'fa-check-double' }} text-success"></i>
                        @endif
                    </div>
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {{ $data->type === $data::OUT ? __('messages.you') . ': ' : '' }}
                        {{ bbCodeTruncate($data->text) }}
                    </div>
                </div>
            </div>
        @endforeach

        {{ $messages->links() }}
    @else
        {{ showError(__('main.empty_messages')) }}
    @endif

    <i class="fa fa-search"></i> <a href="/searchusers">{{ __('index.user_search') }}</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">{{ __('index.contacts') }}</a> / <a href="/ignores">{{ __('index.ignores') }}</a><br>
@stop

@push('scripts')
    <script type="module">
        $(document).ready(function() {
            $(".message-block").click(function() {
                window.location = $(this).data('href');
                return false;
            }).find('a').on('click', function (e) {
                e.stopPropagation();
            });
        });
    </script>
@endpush
