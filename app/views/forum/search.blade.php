@extends('layout')

@section('title')
    Поиск по форуму - @parent
@stop

@section('content')
    <h1>Поиск по форуму</h1>

    <div class="form">
        <form action="/forum/search" method="get">
            <div class="form-group{{ App::hasError('find') }}">
                <label for="inputFind">Запрос</label>
                <input name="find" type="text" class="form-control" id="inputFind" maxlength="50" placeholder="Введите запрос" value="{{ App::getInput('find') }}" required>
                {!! App::textError('find') !!}
            </div>

            <div class="form-group{{ App::hasError('section') }}">
                <label for="inputSection">Раздел</label>
                <select class="form-control" id="inputSection" name="section">
                    <option value="0">Не имеет значения</option>

                    <?php foreach ($forums as $data): ?>
                        <?php $selected = (App::getInput('section') == $data['id'] || $fid == $data['id']) ? ' selected' : ''; ?>

                        <option value="<?=$data['id']?>"<?=$selected?>><?=$data['title']?></option>

                        <?php if ($data->children): ?>
                            <?php foreach($data->children as $datasub): ?>
                                <?php $selected = (App::getInput('section') == $data['id'] || $fid == $datasub['id']) ? ' selected' : ''; ?>

                                <option value="<?=$datasub['id']?>"<?=$selected?>>– <?=$datasub['title']?></option>

                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </select>
                {!! App::textError('section') !!}
            </div>

            <div class="form-group{{ App::hasError('period') }}">
                <label for="inputPeriod">Период</label>
                <select class="form-control" id="inputPeriod" name="period">
                    <option value="0"{{ (App::getInput('period') == 0 ? ' selected' : '') }}>За все время</option>
                    <option value="7"{{ (App::getInput('period') == 7 ? ' selected' : '') }}>Последние 7 дней</option>
                    <option value="30"{{ (App::getInput('period') ==30 ? ' selected' : '') }}>Последние 30 дней</option>
                    <option value="60"{{ (App::getInput('period') == 60 ? ' selected' : '') }}>Последние 60 дней</option>
                    <option value="90"{{ (App::getInput('period') == 90 ? ' selected' : '') }}>Последние 90 дней</option>
                    <option value="180"{{ (App::getInput('period') == 180 ? ' selected' : '') }}>Последние 180 дней</option>
                    <option value="365"{{ (App::getInput('period') == 365 ? ' selected' : '') }}>Последние 365 дней</option>
                </select>
                {!! App::textError('period') !!}
            </div>

            Искать:<br />
            <div class="radio">
                <label>
                    <input type="radio" name="where" value="0"{{ (App::getInput('where') == 0 ? ' checked' : '') }}>
                    В темах
                </label>
            </div>

            <div class="radio">
                <label>
                    <input type="radio" name="where" value="1"{{ (App::getInput('where') == 1 ? ' checked' : '') }}>
                    В сообщениях
                </label>
            </div>


            Тип запроса:<br />
            <div class="radio">
                <label>
                    <input type="radio" name="type" value="0"{{ (App::getInput('type') == 0 ? ' checked' : '') }}>
                    И
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="type" value="1"{{ (App::getInput('type') == 1 ? ' checked' : '') }}>
                    Или
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="type" value="2"{{ (App::getInput('type') == 2 ? ' checked' : '') }}>
                    Полный
                </label>
            </div>

            <button class="btn btn-primary">Найти</button>
        </form>
    </div>
@stop
