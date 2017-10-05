@extends('layout')

@section('title')
    Поиск по форуму
@stop

@section('content')
    <h1>Поиск по форуму</h1>

    <div class="form">
        <form action="/forum/search">
            <div class="form-group{{ hasError('find') }}">
                <label for="inputFind">Запрос</label>
                <input name="find" class="form-control" id="inputFind" maxlength="50" placeholder="Введите запрос" value="{{ getInput('find') }}" required>
                {!! textError('find') !!}
            </div>

            <div class="form-group{{ hasError('section') }}">
                <label for="inputSection">Раздел</label>
                <select class="form-control" id="inputSection" name="section">
                    <option value="0">Не имеет значения</option>

                    @foreach ($forums as $data)
                        <?php $selected = (getInput('section') == $data['id'] || $fid == $data['id']) ? ' selected' : ''; ?>

                        <option value="{{ $data['id'] }}"{{ $selected }}>{{ $data['title'] }}</option>

                        @if ($data->children)
                            @foreach($data->children as $datasub)
                                <?php $selected = (getInput('section') == $data['id'] || $fid == $datasub['id']) ? ' selected' : ''; ?>

                                <option value="{{ $datasub['id'] }}"{{ $selected }}>– {{ $datasub['title'] }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('section') !!}
            </div>

            <div class="form-group{{ hasError('period') }}">
                <label for="inputPeriod">Период</label>
                <select class="form-control" id="inputPeriod" name="period">
                    <option value="0"{{ (getInput('period') == 0 ? ' selected' : '') }}>За все время</option>
                    <option value="7"{{ (getInput('period') == 7 ? ' selected' : '') }}>Последние 7 дней</option>
                    <option value="30"{{ (getInput('period') ==30 ? ' selected' : '') }}>Последние 30 дней</option>
                    <option value="60"{{ (getInput('period') == 60 ? ' selected' : '') }}>Последние 60 дней</option>
                    <option value="90"{{ (getInput('period') == 90 ? ' selected' : '') }}>Последние 90 дней</option>
                    <option value="180"{{ (getInput('period') == 180 ? ' selected' : '') }}>Последние 180 дней</option>
                    <option value="365"{{ (getInput('period') == 365 ? ' selected' : '') }}>Последние 365 дней</option>
                </select>
                {!! textError('period') !!}
            </div>

            Искать:<br>
            <div class="radio">
                <label>
                    <input type="radio" name="where" value="0"{{ (getInput('where') == 0 ? ' checked' : '') }}>
                    В темах
                </label>
            </div>

            <div class="radio">
                <label>
                    <input type="radio" name="where" value="1"{{ (getInput('where') == 1 ? ' checked' : '') }}>
                    В сообщениях
                </label>
            </div>


            Тип запроса:<br>
            <div class="radio">
                <label>
                    <input type="radio" name="type" value="0"{{ (getInput('type') == 0 ? ' checked' : '') }}>
                    И
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="type" value="1"{{ (getInput('type') == 1 ? ' checked' : '') }}>
                    Или
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="type" value="2"{{ (getInput('type') == 2 ? ' checked' : '') }}>
                    Полный
                </label>
            </div>

            <button class="btn btn-primary">Найти</button>
        </form>
    </div>
@stop
