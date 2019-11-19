@extends('layout')

@section('title')
    {{ __('index.admin_advertising') }}
@stop

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
    <div class="form">

        @if ($advert->id)
            @if ($advert->deleted_at > SITETIME)
                <div class="bg-success text-light p-1 mb-3">
                    {{ __('adverts.expires') }}: {{ dateFixed($advert->deleted_at) }}
                </div>
            @else
                <div class="bg-danger text-light p-1 mb-3">
                    {{ __('adverts.expired') }}: {{ dateFixed($advert->deleted_at) }}
                </div>
            @endif
        @endif

        <form action="/admin/admin-adverts" method="post">
            @csrf
            <div class="form-group{{ hasError('site') }}">
                <label for="site">{{ __('adverts.link') }}:</label>
                <input class="form-control" id="site" name="site" type="text" value="{{ getInput('site', $advert->site) }}" maxlength="50" required>
                <div class="invalid-feedback">{{ textError('site') }}</div>
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">{{ __('adverts.name') }}:</label>
                <input class="form-control" id="name" name="name" type="text" maxlength="35" value="{{ getInput('name', $advert->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="form-group{{ hasError('color') }}">
                <label for="color">{{ __('adverts.color') }}:</label>

                <div class="input-group colorpick">
                    <input class="form-control col-sm-4" id="color" name="color" type="text" maxlength="7" value="{{ getInput('color', $advert->color) }}">
                    <div class="input-group-append">
                        <span class="input-group-text input-group-addon"><i></i></span>
                    </div>
                </div>

                <div class="invalid-feedback">{{ textError('color') }}</div>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="bold">
                <input type="checkbox" class="custom-control-input" value="1" name="bold" id="bold"{{ getInput('bold', $advert->bold) ? ' checked' : '' }}>
                <label class="custom-control-label" for="bold">{{ __('adverts.bold') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.save') }}</button>
        </form>
    </div>
@stop
