@extends('layout')

@section('title')
    Поиск по форуму
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">Форум</a></li>
            <li class="breadcrumb-item active">Поиск по форуму</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/forums/search">
            <input type="hidden" name="fid" value="{{ $fid }}">

            <div class="form-group{{ hasError('find') }}">
                <label for="inputFind">Запрос</label>
                <input name="find" class="form-control" id="inputFind" maxlength="50" placeholder="Введите запрос" value="{{ getInput('find') }}" required>
                {!! textError('find') !!}
            </div>

            <div class="form-group{{ hasError('section') }}">
                <label for="inputSection">Раздел</label>
                <?php $inputSection = (int) getInput('section', $fid); ?>

                <select class="form-control" id="inputSection" name="section">
                    <option value="0">Не имеет значения</option>

                    @foreach ($forums as $data)
                        <?php $selected = ($inputSection === $data->id) ? ' selected' : ''; ?>

                        <option value="{{ $data->id }}"{{ $selected }}>{{ $data->title }}</option>

                        @if ($data->children)
                            @foreach($data->children as $datasub)
                                <?php $selected = ($inputSection === $datasub->id) ? ' selected' : ''; ?>

                                <option value="{{ $datasub->id }}"{{ $selected }}>– {{ $datasub->title }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('section') !!}
            </div>

            <div class="form-group{{ hasError('period') }}">
                <label for="inputPeriod">Период</label>

                <?php $inputPeriod = (int) getInput('period'); ?>
                <select class="form-control" id="inputPeriod" name="period">
                    <option value="0"{{ $inputPeriod === 0 ? ' selected' : '' }}>За все время</option>
                    <option value="7"{{ $inputPeriod === 7 ? ' selected' : '' }}>Последние 7 дней</option>
                    <option value="30"{{ $inputPeriod ===30 ? ' selected' : '' }}>Последние 30 дней</option>
                    <option value="60"{{ $inputPeriod === 60 ? ' selected' : '' }}>Последние 60 дней</option>
                    <option value="90"{{ $inputPeriod === 90 ? ' selected' : '' }}>Последние 90 дней</option>
                    <option value="180"{{ $inputPeriod === 180 ? ' selected' : '' }}>Последние 180 дней</option>
                    <option value="365"{{ $inputPeriod === 365 ? ' selected' : '' }}>Последние 365 дней</option>
                </select>
                {!! textError('period') !!}
            </div>

            Искать:<br>
            <?php $inputWhere = (int) getInput('where'); ?>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputWhere0" name="where" value="0"{{ $inputWhere === 0 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputWhere0">В темах</label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputWhere1" name="where" value="1"{{ $inputWhere === 1 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputWhere1">В сообщениях</label>
            </div>

            Тип запроса:<br>
            <?php $inputType = (int) getInput('type'); ?>
            <div class="form-group{{ hasError('type') }}">
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType0" name="type" value="0"{{ $inputType === 0 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType0">И</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType1" name="type" value="1"{{ $inputType === 1 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType1">Или</label>
                </div>
                <div class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" id="inputType2" name="type" value="2"{{ $inputType === 2 ? ' checked' : '' }}>
                    <label class="custom-control-label" for="inputType2">Полный</label>
                </div>
                {!! textError('type') !!}
            </div>

            <button class="btn btn-primary">Найти</button>
        </form>
    </div>
@stop
