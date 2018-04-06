@extends('layout')

@section('title')
    Поиск по форуму
@stop

@section('content')
    <h1>Поиск по форуму</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forum">Форум</a></li>
            <li class="breadcrumb-item active">Поиск по форуму</li>
        </ol>
    </nav>

    <div class="form">
        <form action="/forum/search">
            <div class="form-group{{ hasError('find') }}">
                <label for="inputFind">Запрос</label>
                <input name="find" class="form-control" id="inputFind" maxlength="50" placeholder="Введите запрос" value="{{ getInput('find') }}" required>
                {!! textError('find') !!}
            </div>

            <div class="form-group{{ hasError('section') }}">
                <label for="inputSection">Раздел</label>
                <?php $inputSection = getInput('section', $fid); ?>

                <select class="form-control" id="inputSection" name="section">
                    <option value="0">Не имеет значения</option>

                    @foreach ($forums as $data)
                        <?php $selected = ($inputSection == $data->id) ? ' selected' : ''; ?>

                        <option value="{{ $data->id }}"{{ $selected }}>{{ $data->title }}</option>

                        @if ($data->children)
                            @foreach($data->children as $datasub)
                                <?php $selected = ($inputSection == $datasub->id) ? ' selected' : ''; ?>

                                <option value="{{ $datasub->id }}"{{ $selected }}>– {{ $datasub->title }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('section') !!}
            </div>

            <div class="form-group{{ hasError('period') }}">
                <label for="inputPeriod">Период</label>

                <?php $inputPeriod = getInput('period'); ?>
                <select class="form-control" id="inputPeriod" name="period">
                    <option value="0"{{ $inputPeriod == 0 ? ' selected' : '' }}>За все время</option>
                    <option value="7"{{ $inputPeriod == 7 ? ' selected' : '' }}>Последние 7 дней</option>
                    <option value="30"{{ $inputPeriod ==30 ? ' selected' : '' }}>Последние 30 дней</option>
                    <option value="60"{{ $inputPeriod == 60 ? ' selected' : '' }}>Последние 60 дней</option>
                    <option value="90"{{ $inputPeriod == 90 ? ' selected' : '' }}>Последние 90 дней</option>
                    <option value="180"{{ $inputPeriod == 180 ? ' selected' : '' }}>Последние 180 дней</option>
                    <option value="365"{{ $inputPeriod == 365 ? ' selected' : '' }}>Последние 365 дней</option>
                </select>
                {!! textError('period') !!}
            </div>

            Искать:<br>
            <?php $inputWhere = getInput('where'); ?>
            <div class="radio">
                <label>
                    <input type="radio" name="where" value="0"{{ $inputWhere == 0 ? ' checked' : '' }}>
                    В темах
                </label>
            </div>

            <div class="radio">
                <label>
                    <input type="radio" name="where" value="1"{{ $inputWhere == 1 ? ' checked' : '' }}>
                    В сообщениях
                </label>
            </div>

            Тип запроса:<br>
            <?php $inputType = getInput('type'); ?>
            <div class="radio">
                <label>
                    <input type="radio" name="type" value="0"{{ $inputType == 0 ? ' checked' : '' }}>
                    И
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="type" value="1"{{ $inputType == 1 ? ' checked' : '' }}>
                    Или
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="type" value="2"{{ $inputType == 2 ? ' checked' : '' }}>
                    Полный
                </label>
            </div>

            <button class="btn btn-primary">Найти</button>
        </form>
    </div>
@stop
