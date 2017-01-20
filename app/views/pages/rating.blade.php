@extends('layout')

@section('title', 'Изменения репутации пользователя '.$login.' - @parent')

@section('content')

    <div class="b">{!! user_avatars($login) !!} <b>{{ nickname($login) }} </b> {{ user_visit($login) }}</div>

    <div class="form">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}" />
            <label for="inputRating">Рейтинг</label>
            <select class="form-control" id="inputRating" name="vote">
                <?php $selected = ($vote == 1) ? ' selected="selected"' : ''; ?>
                echo '<option value="1"{{ $selected }}>Плюс</option>';
                <?php $selected = ($vote == 0) ? ' selected="selected"' : ''; ?>
                <option value="0"{{ $selected }}>Минус</option>
            </select>

            <div class="form-group{{ App::hasError('text') }}">
                <label for="markItUp">Комментарий:</label>
                <textarea class="form-control" id="markItUp" cols="25" rows="5" name="text">{{ App::getInput('text') }}</textarea>
                {!! App::textError('text') !!}
            </div>

            <button type="submit" class="btn btn-primary">Продолжить</button>
        </form>
    </div><br />

    <i class="fa fa-briefcase"></i> <a href="/rathist?uz={{ $login }}">История</a><br />
    <i class="fa fa-arrow-circle-left"></i> <a href="/user/{{ $login }}">Вернуться</a><br />
@stop
