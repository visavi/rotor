@extends('layout')

@section('title')
    {{ trans('index.pending_list') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.pending_list') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3 font-weight-bold">

        <i class="fa fa-exclamation-circle"></i>

        @if (setting('regkeys'))
            <span class="text-success">{{ trans('admin.reglists.enabled') }}</span>
        @else
            <span class="text-danger">{{ trans('admin.reglists.disabled') }}</span>
        @endif
    </div>

    @if ($users->isNotEmpty())

        <form action="/admin/reglists?page={{ $page->current }}" method="post">
           @csrf
            @foreach ($users as $user)
                <div class="b">
                    <input type="checkbox" name="choice[]" value="{{ $user->id }}">
                     {!! $user->getGender() !!} <b>{!! $user->getProfile() !!}</b>
                    ({{ trans('users.email') }}: {{ $user->email }})
                </div>

                <div>{{ trans('main.registration_date') }}: {{ dateFixed($user->created_at, 'd.m.Y') }}</div>
            @endforeach

            <?php $inputAction = getInput('action'); ?>
            <div class="form-inline mt-3">
                <div class="form-group{{ hasError('action') }}">
                    <select class="form-control" name="action">
                        <option>{{ trans('main.action') }}</option>
                        <option value="yes"{{ $inputAction === 'yes' ? ' selected' : '' }}>{{ trans('main.allow') }}</option>
                        <option value="no"{{ $inputAction === 'no' ? ' selected' : '' }}>{{ trans('main.disallow') }}</option>
                    </select>
                </div>

                <button class="btn btn-primary">{{ trans('main.execute') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('action') }}</div>
        </form>

        {!! pagination($page) !!}

        {{ trans('main.total') }}: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError(trans('admin.reglists.empty_users')) !!}
    @endif
@stop
