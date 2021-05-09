@extends('layout')

@section('title', __('statuses.edit_status'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/status">{{ __('index.user_statuses') }}</a></li>
            <li class="breadcrumb-item active">{{ __('statuses.edit_status') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form method="post">
            @csrf
            <div class="mb-3">
                <label for="inputFrom" class="form-label">{{ __('main.from') }}:</label>
                <input type="text" pattern="\d*" maxlength="10" class="form-control" id="inputFrom" name="topoint" placeholder="От" value="{{ getInput('topoint', $status->topoint) }}">

                <label for="inputTo" class="form-label">{{ __('main.to') }}:</label>
                <input type="text" pattern="\d*" maxlength="10" class="form-control" id="inputTo" name="point" placeholder="До" value="{{ getInput('point', $status->point) }}">
            </div>

            <div class="mb-3{{ hasError('name') }}">
                <label for="inputName" class="form-label">{{ __('main.status') }}:</label>
                <input type="text" maxlength="30" class="form-control" id="inputName" name="name" placeholder="Статус" value="{{ getInput('name', $status->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <?php $color = getInput('color', $status->color); ?>
            <div class="col-sm-4 mb-3{{ hasError('color') }}">
                <label for="color" class="form-label">{{ __('main.color') }}:</label>
                <div class="input-group">
                    <input type="text" name="color" class="form-control colorpicker" id="color" maxlength="7" value="{{ $color }}">
                    <input type="color" class="form-control form-control-color colorpicker-addon" value="{{ $color }}">
                </div>
                <div class="invalid-feedback">{{ textError('color') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
