@extends('layout')

@section('title')
    Ответ на предложение
@stop

@section('content')

    <h1>Ответ на предложение</h1>

    <div class="form">
        <form action="/admin/offers/reply/{{ $offer->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('reply') }}">
                <label for="reply">Ответ:</label>
                <textarea class="form-control markItUp" id="reply" rows="5" name="reply" required>{{ getInput('reply', $offer->reply) }}</textarea>
                {!! textError('reply') !!}
            </div>

            <div class="form-group{{ hasError('status') }}">
                <label for="status">Статус:</label>

                <?php $inputStatus = getInput('status', $offer->status); ?>
                <select class="form-control" name="status" id="status">
                    @foreach ($statuses as $key => $status)
                        <?php $selected = ($key == $inputStatus) ? ' selected' : ''; ?>
                        <option value="{{ $key }}"{{ $selected }}>{{ $status }}</option>
                    @endforeach
                </select>

                {!! textError('status') !!}
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $offer->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">Закрыть комментарии</label>
            </div>

            <button class="btn btn-primary">Ответить</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers/{{ $offer->id }}">Вернуться</a><br>
@stop
