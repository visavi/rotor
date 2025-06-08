@extends('layout')

@section('title', __('index.blacklist'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.blacklist') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <?php $active = ($type === 'email') ? 'primary' : 'adaptive'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="/admin/blacklists?type=email">{{ __('admin.blacklists.email') }}</a>
        <?php $active = ($type === 'login') ? 'primary' : 'adaptive'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="/admin/blacklists?type=login">{{ __('admin.blacklists.logins') }}</a>
        <?php $active = ($type === 'domain') ? 'primary' : 'adaptive'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="/admin/blacklists?type=domain">{{ __('admin.blacklists.domains') }}</a>
    </div>

    @if ($lists->isNotEmpty())
        <form action="/admin/blacklists/delete?type={{ $type }}&amp;page={{ $lists->currentPage() }}" method="post">
            @csrf
            @foreach ($lists as $list)
                <div class="section mb-3 shadow">
                    <div class="section-title">
                        <i class="fa fa-pencil-alt"></i> {{ $list->value }}

                        <div class="float-end">
                            <input type="checkbox" class="form-check-input" name="del[]" value="{{ $list->id }}">
                        </div>
                    </div>

                    <div class="section-content">
                        {{ __('main.added') }}: {{ $list->user->getProfile() }} ({{ dateFixed($list->created_at) }})
                    </div>
                </div>
            @endforeach

            <div class="clearfix mb-3">
                <button class="btn btn-sm btn-danger float-end">{{ __('main.delete_selected') }}</button>
            </div>
        </form>

        {{ $lists->links() }}

        <div class="mb-3">
            {{ __('main.total') }}: <b>{{ $lists->total() }}</b>
        </div>
    @else
        {{ showError( __('admin.blacklists.empty_list')) }}
    @endif

    <div class="section-form mb-3 shadow">
        <form action="/admin/blacklists?type={{ $type }}" method="post">
            @csrf
            <div class="input-group{{ hasError('value') }}">
                <input type="text" class="form-control" id="value" name="value" maxlength="100" value="{{ getInput('value') }}" placeholder="{{ __('main.record') }}" required>
                <button class="btn btn-primary">{{ __('main.add') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('value') }}</div>
        </form>
    </div>
@stop
