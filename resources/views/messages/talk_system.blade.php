@extends('layout')

@section('title', __('messages.notifications'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/messages">{{ __('index.messages') }}</a></li>
            <li class="breadcrumb-item active">{{ __('messages.notifications') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($messages->isNotEmpty())
        <div class="mb-3">
            @foreach ($messages as $data)
                <div class="talk-row">
                    <div class="talk-avatar">
                        <span class="avatar-default avatar-guest rounded-circle"><i class="fas fa-headset"></i></span>
                    </div>

                    <div class="section talk-bubble">
                        <div class="talk-head">
                            <b>{{ __('messages.system') }}</b>

                            @unless ($data->reading)
                                <span class="badge bg-info">{{ __('messages.new') }}</span>
                            @endunless
                        </div>

                        <div class="section-message">
                            {{ $data->getText() }}
                        </div>

                        <div class="talk-meta">
                            <span class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $messages->links() }}
    @else
        <div class="section mb-3 shadow">
            <div class="section-body d-flex flex-column align-items-center text-muted py-4">
                <i class="far fa-bell fa-2x mb-2"></i>
                {{ __('messages.empty_notifications') }}
            </div>
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <span class="text-muted">
            {{ __('main.total') }}: <b>{{ $messages->total() }}</b>
            <a class="ms-3" href="/users"><i class="fa fa-search"></i> {{ __('index.user_search') }}</a>
        </span>

        @if ($messages->isNotEmpty())
            <form action="/messages/delete/0" method="post" onsubmit="return confirm('{{ __('messages.delete_confirm') }}')">
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
