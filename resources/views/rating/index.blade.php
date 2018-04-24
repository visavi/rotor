@extends('layout')

@section('title')
    Изменения репутации пользователя {{ $user->login }}
@stop

@section('content')

    <h1>Изменения репутации пользователя {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/user/{{ $user->login }}">{{ $user->login }}</a></li>
            <li class="breadcrumb-item active">Изменения репутации</li>
        </ol>
    </nav>

    <div class="form">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <label for="inputRating">Рейтинг</label>
            <select class="form-control" id="inputRating" name="vote">
                <?php $selected = ($vote === 'plus') ? ' selected' : ''; ?>
                <option value="plus"{{ $selected }}>Плюс</option>
                <?php $selected = ($vote === 'minus') ? ' selected' : ''; ?>
                <option value="minus"{{ $selected }}>Минус</option>
            </select>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Комментарий:</label>
                <textarea class="form-control markItUp" id="text" cols="25" rows="5" name="text">{{ getInput('text') }}</textarea>
                {!! textError('text') !!}
            </div>

            <button class="btn btn-primary">Продолжить</button>
        </form>
    </div><br>

    <i class="fa fa-briefcase"></i> <a href="/rating/{{ $user->login }}">История</a><br>
@stop
