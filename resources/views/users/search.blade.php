@extends('layout')

@section('title', __('index.search_users'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.search_users') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form p-3 shadow">
        <form method="get" action="/searchusers/search">

            <div class="form-group{{ hasError('find') }}">
                <label for="find">{{ __('users.login_or_username') }}:</label>
                <input type="text" class="form-control" id="find" name="find" maxlength="50" placeholder="{{ __('users.login_or_username') }}" value="{{ getInput('find') }}" required>
                <div class="invalid-feedback">{{ textError('find') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.search') }}</button>
        </form>
    </div>

    <a class="badge badge-pill badge-success" href="/searchusers/1">0-9</a>
    <a class="badge badge-pill badge-success" href="/searchusers/a">A</a>
    <a class="badge badge-pill badge-success" href="/searchusers/b">B</a>
    <a class="badge badge-pill badge-success" href="/searchusers/c">C</a>
    <a class="badge badge-pill badge-success" href="/searchusers/d">D</a>
    <a class="badge badge-pill badge-success" href="/searchusers/e">E</a>
    <a class="badge badge-pill badge-success" href="/searchusers/f">F</a>
    <a class="badge badge-pill badge-success" href="/searchusers/g">G</a>
    <a class="badge badge-pill badge-success" href="/searchusers/h">H</a>
    <a class="badge badge-pill badge-success" href="/searchusers/i">I</a>
    <a class="badge badge-pill badge-success" href="/searchusers/j">J</a>
    <a class="badge badge-pill badge-success" href="/searchusers/k">K</a>
    <a class="badge badge-pill badge-success" href="/searchusers/l">L</a>
    <a class="badge badge-pill badge-success" href="/searchusers/m">M</a>
    <a class="badge badge-pill badge-success" href="/searchusers/n">N</a>
    <a class="badge badge-pill badge-success" href="/searchusers/o">O</a>
    <a class="badge badge-pill badge-success" href="/searchusers/p">P</a>
    <a class="badge badge-pill badge-success" href="/searchusers/q">Q</a>
    <a class="badge badge-pill badge-success" href="/searchusers/r">R</a>
    <a class="badge badge-pill badge-success" href="/searchusers/s">S</a>
    <a class="badge badge-pill badge-success" href="/searchusers/t">T</a>
    <a class="badge badge-pill badge-success" href="/searchusers/u">U</a>
    <a class="badge badge-pill badge-success" href="/searchusers/v">V</a>
    <a class="badge badge-pill badge-success" href="/searchusers/w">W</a>
    <a class="badge badge-pill badge-success" href="/searchusers/x">X</a>
    <a class="badge badge-pill badge-success" href="/searchusers/y">Y</a>
    <a class="badge badge-pill badge-success" href="/searchusers/z">Z</a>
    <br><br>

    <p class="text-muted font-italic">
        {!! __('users.search_text') !!}
    </p>
@stop
