@extends('layout')

@section('title', __('index.guestbook'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.guestbook') }}</li>
        </ol>
    </nav>
@stop

@section('header')
    <div class="float-end">
        <a class="btn btn-light" href="{{ route('guestbook.index', ['page' => $posts->currentPage()]) }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.guestbook') }}</h1>
@stop

@section('content')
    @if ($posts->isNotEmpty())
        @if ($unpublished && isAdmin())
            <div class="alert alert-info">
                {{ __('guestbook.require_publish') }}: {{ $unpublished }}
            </div>
        @endif

        <form method="post">
            @csrf
            @foreach ($posts as $post)
                <div class="section mb-3 shadow">
                    <div class="user-avatar">
                        @if ($post->user_id)
                            {{ $post->user->getAvatar() }}
                            {{ $post->user->getOnline() }}
                        @else
                            {{ $post->user->getAvatarGuest() }}
                        @endif
                    </div>

                    <div class="section-user d-flex align-items-start">
                        <div class="flex-grow-1">
                            @if ($post->user_id)
                                {{ $post->user->getProfile() }}
                                <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small><br>
                                <small class="fst-italic">{{ $post->user->getStatus() }}</small>
                            @else
                                @if ($post->guest_name)
                                    <span class="section-author fw-bold" data-login="{{ $post->guest_name }}">{{ $post->guest_name }}</span>
                                @else
                                    <span class="section-author fw-bold" data-login="{{ setting('guestsuser') }}">{{ setting('guestsuser') }}</span>
                                @endif
                                <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small><br>
                                <small class="fst-italic">{{ setting('guestsuser') }}</small>
                            @endif

                            @if (! $post->active)
                                <span class="badge bg-danger">{{ __('guestbook.not_publish') }}</span>
                            @endif
                        </div>

                        <div class="text-end">
                            <a href="{{ route('admin.guestbook.reply', ['id' => $post->id, 'page' => $posts->currentPage()]) }}"><i class="fa fa-reply text-muted"></i></a>
                            <a href="{{ route('admin.guestbook.edit', ['id' => $post->id, 'page' => $posts->currentPage()]) }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                            <input type="checkbox" class="form-check-input" name="chosen[]" value="{{ $post->id }}">
                        </div>
                    </div>

                    <div class="section-body border-top">
                        <div class="section-message">
                            {{ bbCode($post->text) }}
                        </div>

                        @include('app/_media_viewer', ['model' => $post])

                        @if ($post->edit_user_id)
                            <div class="small">
                                <i class="fa fa-exclamation-circle text-danger"></i> {{ __('main.changed') }}: {{ $post->editUser->getName() }}
                                <small class="section-date text-muted fst-italic">{{ dateFixed($post->updated_at) }}</small>
                            </div>
                        @endif

                        @if ($post->reply)
                            <div class="text-danger">{{ __('guestbook.answer') }}: {{ bbCode($post->reply) }}</div>
                        @endif

                        <div class="small text-muted fst-italic mt-2">
                            {{ $post->brow }}, {{ $post->ip }}
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="clearfix mb-3">
                <button class="btn btn-sm btn-danger float-end" formaction="{{ route('admin.guestbook.delete', ['page' => $posts->currentPage()]) }}">{{ __('main.delete_selected') }}</button>

                @if ($unpublished)
                    <button class="btn btn-sm btn-success float-end me-1" formaction="{{ route('admin.guestbook.publish', ['page' => $posts->currentPage()]) }}">{{ __('main.publish') }}</button>
                @endif
            </div>
        </form>

        {{ $posts->links() }}

        <div class="mb-3">
            {{ __('guestbook.total_messages') }}: <b>{{ $posts->total() }}</b>
        </div>

        @if (isAdmin('boss'))
            <form action="{{ route('admin.guestbook.clear') }}" method="post">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('guestbook.confirm_delete') }}')"><i class="fa fa-trash-alt"></i> {{ __('main.clear') }}</button>
            </form><br>
        @endif
    @else
        {{ showError(__('main.empty_messages')) }}
    @endif
@stop
