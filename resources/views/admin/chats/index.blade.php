@extends('layout')

@section('title', __('index.admin_chat'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.admin_chat') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/stickers">{{ __('main.stickers') }}</a> /
    <a href="/tags">{{ __('main.tags') }}</a><hr>

    @if ($posts->isNotEmpty())
        @foreach ($posts as $post)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {!! $post->user->getAvatar() !!}
                    {!! $post->user->getOnline() !!}
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        {!! $post->user->getProfile() !!}
                        <small class="section-date text-muted font-italic">{{ dateFixed($post->created_at) }}</small><br>
                        <small class="font-italic">{!! $post->user->getStatus() !!}</small>
                    </div>

                    @if (getUser() && getUser('id') !== $post->user_id)
                        <div class="text-right">
                            <a href="#" onclick="return postReply(this)" data-toggle="tooltip" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>
                            <a href="#" onclick="return postQuote(this)" data-toggle="tooltip" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>
                        </div>
                    @endif

                    @if ($post->created_at + 600 > SITETIME && getUser() && getUser('id') === $post->user_id)
                        <div class="text-right">
                            <a href="/admin/chats/edit/{{ $post->id }}?page={{ $posts->currentPage() }}" data-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        </div>
                    @endif
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {!! bbCode($post->text) !!}
                    </div>

                    @if ($post->edit_user_id)
                        <div class="small">
                            <i class="fa fa-exclamation-circle text-danger"></i> {{ __('main.changed') }}: {!! $post->editUser->getProfile() !!} ({{ dateFixed($post->updated_at) }})
                        </div>
                    @endif

                    <div class="small text-muted font-italic mt-2">{{ $post->brow }}, {{ $post->ip }}</div>
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('main.empty_messages')) !!}
    @endif

    {{ $posts->links() }}

    <div class="section-form p-3 mb-3 shadow">
        <form action="/admin/chats" method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ __('main.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.write') }}</button>
        </form>
    </div>

    @if ($posts->total() && isAdmin('boss'))
        <i class="fa fa-times"></i> <a href="/admin/chats/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('admin.chat.confirm_clear') }}')">{{ __('admin.chat.clear') }}</a><br>
    @endif
@stop
