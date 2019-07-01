@extends('layout')

@section('title', trans('index.blacklist'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.blacklist') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <?php $active = ($type === 'email') ? 'success' : 'light'; ?>
    <a href="/admin/blacklists?type=email" class="badge badge-{{ $active }}">{{ trans('admin.blacklists.email') }}</a>
    <?php $active = ($type === 'login') ? 'success' : 'light'; ?>
    <a href="/admin/blacklists?type=login" class="badge badge-{{ $active }}">{{ trans('admin.blacklists.logins') }}</a>
    <?php $active = ($type === 'domain') ? 'success' : 'light'; ?>
    <a href="/admin/blacklists?type=domain" class="badge badge-{{ $active }}">{{ trans('admin.blacklists.domains') }}</a>
    <br><br>

    @if ($lists->isNotEmpty())

        <form action="/admin/blacklists/delete?type={{ $type }}&amp;page={{ $page->current }}" method="post">
            @csrf
            @foreach ($lists as $list)
                <div class="b">
                    <input type="checkbox" name="del[]" value="{{ $list->id }}">

                    <i class="fa fa-pencil-alt"></i> <b>{{ $list->value }}</b>
                </div>
                <div>
                    {{ trans('main.added') }}: {!! $list->user->getProfile() !!} ({{ dateFixed($list->created_at) }})
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">{{ trans('main.delete_selected') }}</button>
        </form>

        {!! pagination($page) !!}

    @else
        {!! showError( trans('admin.blacklists.empty_list') ) !!}
    @endif

    <div class="form">
        <form action="/admin/blacklists?type={{ $type }}" method="post">
            @csrf
            <div class="form-inline">
                <div class="form-group{{ hasError('value') }}">
                    <input type="text" class="form-control" id="value" name="value" maxlength="100" value="{{ getInput('value') }}" placeholder="{{ trans('main.record') }}cd -" required>
                </div>

                <button class="btn btn-primary">{{ trans('main.add') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('value') }}</div>
        </form>
    </div><br>

    {{ trans('main.total') }}: <b>{{ $page->total }}</b><br>
@stop
