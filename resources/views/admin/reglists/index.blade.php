@extends('layout')

@section('title', __('index.pending_list'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.pending_list') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3 font-weight-bold">
        <i class="fa fa-exclamation-circle"></i>

        @if (setting('regkeys'))
            <span class="text-success">{{ __('admin.reglists.enabled') }}</span>
        @else
            <span class="text-danger">{{ __('admin.reglists.disabled') }}</span>
        @endif
    </div>

    @if ($users->isNotEmpty())

        <form action="/admin/reglists?page={{ $users->currentPage() }}" method="post">
           @csrf
            @foreach ($users as $user)
                <div class="b">
                    <input type="checkbox" name="choice[]" value="{{ $user->id }}">
                     {!! $user->getGender() !!} {!! $user->getProfile() !!}
                    ({{ __('users.email') }}: {{ $user->email }})
                </div>

                <div>{{ __('main.registration_date') }}: {{ dateFixed($user->created_at, 'd.m.Y') }}</div>
            @endforeach

            <?php $inputAction = getInput('action'); ?>
            <div class="form-inline mt-3">
                <div class="form-group{{ hasError('action') }}">
                    <select class="form-control" name="action">
                        <option>{{ __('main.action') }}</option>
                        <option value="yes"{{ $inputAction === 'yes' ? ' selected' : '' }}>{{ __('main.allow') }}</option>
                        <option value="no"{{ $inputAction === 'no' ? ' selected' : '' }}>{{ __('main.disallow') }}</option>
                    </select>
                </div>

                <button class="btn btn-primary">{{ __('main.execute') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('action') }}</div>
        </form>

        <br>{{ __('main.total') }}: <b>{{ $users->total() }}</b><br>
    @else
        {!! showError(__('admin.reglists.empty_users')) !!}
    @endif

    {{ $users->links() }}
@stop
