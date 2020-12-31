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
                        {!! $contact->contactor->getAvatar() !!}
                        {!! $contact->contactor->getOnline() !!}
                    </div>

                    <div class="section-user d-flex align-items-center">
                        <div class="flex-grow-1">
                            {!! $contact->contactor->getProfile() !!}

                            <small class="section-date text-muted font-italic">{{ dateFixed($contact->created_at) }}</small><br>
                            <small class="font-italic">{!! $contact->contactor->getStatus() !!}</small>
                        </div>

                        <div class="text-right">
                            <a href="/messages/talk/{{ $contact->contactor->login }}" data-toggle="tooltip" title="{{ __('main.write') }}"><i class="fa fa-reply text-muted"></i></a>
                            <a href="/contacts/note/{{ $contact->id }}" data-toggle="tooltip" title="{{ __('main.note') }}"><i class="fa fa-sticky-note text-muted"></i></a>
                            <a href="/transfers?user={{ $contact->contactor->login }}" data-toggle="tooltip" title="{{ __('contacts.transfer') }}"><i class="fas fa-coins text-muted"></i></a>
                            <input type="checkbox" name="del[]" value="{{ $contact->id }}">
                        </div>
                    </div>
                    <div class="section-body border-top">
                        <div class="section-message">
                            @if ($contact->text)
                                {{ __('main.note') }}: {!! bbCode($contact->text) !!}
                            @else
                                {{ __('main.empty_notes') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
            </div>
        </form>

        {{ __('main.total') }}: <b>{{ $contacts->total() }}</b><br>
    @else
        {!! showError(__('contacts.empty_list')) !!}
    @endif

    {{ $contacts->links() }}

    <div class="section-form shadow my-3">
        <form method="post">
            @csrf
            <div class="input-group{{ hasError('user') }}">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-pencil-alt"></i></span>
                </div>

                <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user', $login) }}" placeholder="{{ __('main.user_login') }}" required>

                <div class="input-group-append">
                    <button class="btn btn-primary">{{ __('main.add') }}</button>
                </div>
            </div>
            <div class="invalid-feedback">{{ textError('user') }}</div>
        </form>
    </div>

    <i class="fa fa-ban"></i> <a href="/ignores">{{ __('index.ignores') }}</a><br>
    <i class="fa fa-envelope"></i> <a href="/messages">{{ __('index.messages') }}</a><br>
@stop
