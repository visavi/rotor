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
    <div class="section-form mb-3 shadow">
        <form method="get" action="/searchusers/search">

            <div class="mb-3{{ hasError('find') }}">
                <label for="find" class="form-label">{{ __('users.login_or_username') }}:</label>
                <input type="text" class="form-control" id="find" name="find" maxlength="50" placeholder="{{ __('users.login_or_username') }}" value="{{ getInput('find') }}" required>
                <div class="invalid-feedback">{{ textError('find') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.search') }}</button>
        </form>
    </div>

    <a class="badge rounded-pill bg-success" href="/searchusers/sort/1">0-9</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/a">A</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/b">B</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/c">C</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/d">D</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/e">E</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/f">F</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/g">G</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/h">H</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/i">I</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/j">J</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/k">K</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/l">L</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/m">M</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/n">N</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/o">O</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/p">P</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/q">Q</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/r">R</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/s">S</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/t">T</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/u">U</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/v">V</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/w">W</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/x">X</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/y">Y</a>
    <a class="badge rounded-pill bg-success" href="/searchusers/sort/z">Z</a>
    <br><br>

    <p class="text-muted fst-italic">
        {!! __('users.search_text') !!}
    </p>
@stop
