@extends('layout')

@section('title')
    {{ trans('adverts.edit_advert') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/adverts">{{ trans('adverts.user_advert') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('adverts.edit_advert') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/adverts/edit/{{ $link->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('site') }}">
                <label for="site">{{ trans('adverts.link') }}:</label>
                <input class="form-control" id="site" name="site" type="text" value="{{ getInput('site', $link->site) }}" maxlength="50" required>
                {!! textError('site') !!}
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">{{ trans('adverts.name') }}:</label>
                <input class="form-control" id="name" name="name" type="text" maxlength="35" value="{{ getInput('name', $link->name) }}" required>
                {!! textError('name') !!}
            </div>

            <div class="form-group{{ hasError('color') }}">
                <label for="color">{{ trans('adverts.color') }}:</label>

                <div class="input-group colorpick">
                    <input class="form-control col-sm-4" id="color" name="color" type="text" maxlength="7" value="{{ getInput('color', $link->color) }}">
                    <div class="input-group-append">
                        <span class="input-group-text input-group-addon"><i></i></span>
                    </div>
                </div>

                {!! textError('color') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="bold">
                <input type="checkbox" class="custom-control-input" value="1" name="bold" id="bold"{{ getInput('bold', $link->bold) ? ' checked' : '' }}>
                <label class="custom-control-label" for="bold">{{ trans('adverts.bold') }}</label>
            </div>

            <button class="btn btn-primary">{{ trans('main.change') }}</button>
        </form>
    </div>
@stop
