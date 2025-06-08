@extends('layout')

@section('title', __('adverts.edit_advert'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/adverts">{{ __('index.advertising') }}</a></li>
            <li class="breadcrumb-item active">{{ __('adverts.edit_advert') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="/admin/adverts/edit/{{ $link->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('site') }}">
                <label for="site" class="form-label">{{ __('adverts.link') }}:</label>
                <input class="form-control" id="site" name="site" type="text" value="{{ getInput('site', $link->site) }}" maxlength="50" required>
                <div class="invalid-feedback">{{ textError('site') }}</div>
            </div>

            <div class="mb-3{{ hasError('name') }}">
                <label for="name" class="form-label">{{ __('adverts.name') }}:</label>
                <input class="form-control" id="name" name="name" type="text" maxlength="35" value="{{ getInput('name', $link->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <?php $color = getInput('color', $link->color); ?>
            <div class="col-sm-4 mb-3{{ hasError('color') }}">
                <label for="color" class="form-label">{{ __('adverts.color') }}:</label>
                <div class="input-group">
                    <input type="text" name="color" class="form-control colorpicker" id="color" maxlength="7" value="{{ $color }}">
                    <input type="color" class="form-control form-control-color colorpicker-addon" value="{{ $color }}">
                </div>
                <div class="invalid-feedback">{{ textError('color') }}</div>
            </div>

            <div class="form-check">
                <input type="hidden" value="0" name="bold">
                <input type="checkbox" class="form-check-input" value="1" name="bold" id="bold"{{ getInput('bold', $link->bold) ? ' checked' : '' }}>
                <label class="form-check-label" for="bold">{{ __('adverts.bold') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
