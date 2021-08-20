@extends('layout')

@section('title', __('index.ignores'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.ignores') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($ignores->isNotEmpty())
        <form action="/ignores/delete?page={{ $ignores->currentPage() }}" method="post">
            @csrf
            @foreach ($ignores as $ignore)
                <div class="section mb-3 shadow">
                    <div class="user-avatar">
                        {{ $ignore->ignoring->getAvatar() }}
                        {{ $ignore->ignoring->getOnline() }}
                    </div>

                    <div class="section-user d-flex align-items-center">
                        <div class="flex-grow-1">
                            {{ $ignore->ignoring->getProfile() }}

                            <small class="section-date text-muted fst-italic">{{ dateFixed($ignore->created_at) }}</small><br>
                            <small class="fst-italic">{{ $ignore->ignoring->getStatus() }}</small>
                        </div>

                        <div class="text-end">
                            <a href="/messages/talk/{{ $ignore->ignoring->login }}" data-bs-toggle="tooltip" title="{{ __('main.write') }}"><i class="fa fa-reply text-muted"></i></a>
                            <a href="/ignores/note/{{ $ignore->id }}" data-bs-toggle="tooltip" title="{{ __('main.note') }}"><i class="fa fa-sticky-note text-muted"></i></a>
                            <input type="checkbox" class="form-check-input" name="del[]" value="{{ $ignore->id }}">
                        </div>
                    </div>
                    <div class="section-body border-top">
                        <div class="section-message">
                            @if ($ignore->text)
                                {{ __('main.note') }}: {{ bbCode($ignore->text) }}
                            @else
                                {{ __('main.empty_notes') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="float-end">
                <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
            </div>
        </form>

        {{ $ignores->links() }}

        <div class="mb-3">
            {{ __('main.total') }}: <b>{{ $ignores->total() }}</b>
        </div>
    @else
        {{ showError(__('ignores.empty_list')) }}
    @endif

    <div class="section-form my-3 shadow">
        <form method="post">
            @csrf
            <div class="input-group{{ hasError('user') }}">
                <span class="input-group-text"><i class="fa fa-pencil-alt"></i></span>
                <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user', $login) }}" placeholder="{{ __('main.user_login') }}" required>
                <button class="btn btn-primary">{{ __('main.add') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('user') }}</div>
        </form>
    </div>

    <i class="fa fa-users"></i> <a href="/contacts">{{ __('index.contacts') }}</a><br>
    <i class="fa fa-envelope"></i> <a href="/messages">{{ __('index.messages') }}</a><br>
@stop
