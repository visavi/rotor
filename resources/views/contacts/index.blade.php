@extends('layout')

@section('title', __('index.contacts'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.contacts') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($contacts->isNotEmpty())
        <form action="/contacts/delete?page={{ $contacts->currentPage() }}" method="post">
            @csrf
            @foreach ($contacts as $contact)
                <div class="section mb-3 shadow">
                    <div class="user-avatar">
                        {{ $contact->contactor->getAvatar() }}
                        {{ $contact->contactor->getOnline() }}
                    </div>

                    <div class="section-user d-flex align-items-start">
                        <div class="flex-grow-1">
                            {{ $contact->contactor->getProfile() }}

                            <small class="section-date text-muted fst-italic">{{ dateFixed($contact->created_at) }}</small><br>
                            <small class="fst-italic">{{ $contact->contactor->getStatus() }}</small>
                        </div>

                        <div class="text-end">
                            <a href="/messages/talk/{{ $contact->contactor->login }}" data-bs-toggle="tooltip" title="{{ __('main.write') }}"><i class="fa fa-reply text-muted"></i></a>
                            <a href="/contacts/note/{{ $contact->id }}" data-bs-toggle="tooltip" title="{{ __('main.note') }}"><i class="fa fa-sticky-note text-muted"></i></a>
                            <a href="/transfers?user={{ $contact->contactor->login }}" data-bs-toggle="tooltip" title="{{ __('contacts.transfer') }}"><i class="fas fa-coins text-muted"></i></a>
                            <input type="checkbox" class="form-check-input" name="del[]" value="{{ $contact->id }}">
                        </div>
                    </div>
                    <div class="section-body border-top">
                        <div class="section-message">
                            @if ($contact->text)
                                {{ __('main.note') }}: {{ bbCode($contact->text) }}
                            @else
                                {{ __('main.empty_notes') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="clearfix mb-3">
                <button class="btn btn-sm btn-danger float-end">{{ __('main.delete_selected') }}</button>
            </div>
        </form>

        {{ $contacts->links() }}

        <div class="mb-3">
            {{ __('main.total') }}: <b>{{ $contacts->total() }}</b>
        </div>
    @else
        {{ showError(__('contacts.empty_list')) }}
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

    <i class="fa fa-ban"></i> <a href="/ignores">{{ __('index.ignores') }}</a><br>
    <i class="fa fa-envelope"></i> <a href="/messages">{{ __('index.messages') }}</a><br>
@stop
