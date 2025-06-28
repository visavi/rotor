@extends('layout')

@section('title', __('index.admin_advertising'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
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
                <div class="alert alert-warning">
                    {{ __('adverts.expired') }}: {{ dateFixed($advert->deleted_at) }}
                </div>
            @endif

            <i class="fas fa-times"></i> <a class="me-3" href="/admin/admin-adverts/delete?_token={{ csrf_token() }}" onclick="return confirm('{{ __('main.delete') }}')">{{ __('main.delete') }}</a>
            <hr>
        @endif

        <form action="/admin/admin-adverts" method="post">
            @csrf
            <div class="mb-3">
                <label for="site" class="form-label">{{ __('adverts.link') }}:</label>
                <input class="form-control{{ hasError('site') }}" id="site" name="site" type="text" value="{{ old('site', $advert->site) }}" maxlength="50" required>
                <div class="invalid-feedback">{{ textError('site') }}</div>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">{{ __('adverts.name') }}:</label>
                <input class="form-control{{ hasError('site') }}" id="name" name="name" type="text" maxlength="35" value="{{ old('name', $advert->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <?php $color = old('color', $advert->color); ?>
            <div class="col-sm-4 mb-3{{ hasError('color') }}">
                <label for="color" class="form-label">{{ __('adverts.color') }}:</label>
                <div class="input-group">
                    <input type="text" name="color" class="form-control colorpicker" id="color" maxlength="7" value="{{ $color }}">
                    <input type="color" class="form-control form-control-color colorpicker-addon" value="{{ $color }}">
                    <div class="invalid-feedback">{{ textError('color') }}</div>
                </div>
            </div>

            <div class="form-check">
                <input type="hidden" value="0" name="bold">
                <input type="checkbox" class="form-check-input" value="1" name="bold" id="bold"{{ old('bold', $advert->bold) ? ' checked' : '' }}>
                <label class="form-check-label" for="bold">{{ __('adverts.bold') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.save') }}</button>
        </form>
    </div>
@stop
