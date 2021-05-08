@extends('layout')

@section('title', __('index.admin_advertising'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.admin_advertising') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        @if ($advert->id)
            @if ($advert->deleted_at > SITETIME)
                <div class="alert alert-success">
                    {{ __('adverts.expires') }}: {{ dateFixed($advert->deleted_at) }}
                </div>
            @else
                <div class="alert alert-danger">
                    {{ __('adverts.expired') }}: {{ dateFixed($advert->deleted_at) }}
                </div>
            @endif
        @endif

        <form action="/admin/admin-adverts" method="post">
            @csrf
            <div class="mb-3{{ hasError('site') }}">
                <label for="site" class="form-label">{{ __('adverts.link') }}:</label>
                <input class="form-control" id="site" name="site" type="text" value="{{ getInput('site', $advert->site) }}" maxlength="50" required>
                <div class="invalid-feedback">{{ textError('site') }}</div>
            </div>

            <div class="mb-3{{ hasError('name') }}">
                <label for="name" class="form-label">{{ __('adverts.name') }}:</label>
                <input class="form-control" id="name" name="name" type="text" maxlength="35" value="{{ getInput('name', $advert->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="mb-3{{ hasError('color') }}">
                <label for="color" class="form-label">{{ __('adverts.color') }}:</label>

                <div class="input-group colorpick">
                    <input class="form-control col-sm-4" id="color" name="color" type="text" maxlength="7" value="{{ getInput('color', $advert->color) }}">
                    <span class="input-group-append">
                        <span class="input-group-text colorpicker-input-addon"><i></i></span>
                    </span>
                </div>

                <div class="invalid-feedback">{{ textError('color') }}</div>
            </div>

            <div class="form-check">
                <input type="hidden" value="0" name="bold">
                <input type="checkbox" class="form-check-input" value="1" name="bold" id="bold"{{ getInput('bold', $advert->bold) ? ' checked' : '' }}>
                <label class="form-check-label" for="bold">{{ __('adverts.bold') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.save') }}</button>
        </form>
    </div>
@stop
