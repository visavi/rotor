@extends('layout')

@section('title')
    Поиск по файлам
@stop

@section('content')
    <h1>Поиск по файлам</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>
            <li class="breadcrumb-item active">Поиск</li>
        </ol>
    </nav>

    <div class="form">
        <form action="/load/search">
            <div class="form-group{{ hasError('find') }}">
                <label for="inputFind">Запрос</label>
                <input name="find" class="form-control" id="inputFind" maxlength="50" placeholder="Введите запрос" value="{{ getInput('find') }}" required>
                {!! textError('find') !!}
            </div>

            <div class="form-group{{ hasError('section') }}">
                <label for="inputSection">Раздел</label>
                <?php $inputSection = getInput('section', $cid); ?>

                <select class="form-control" id="inputSection" name="section">
                    <option value="0">Не имеет значения</option>

                    @foreach ($categories as $data)
                        <?php $selected = ($inputSection == $data->id) ? ' selected' : ''; ?>

                        <option value="{{ $data->id }}"{{ $selected }}>{{ $data->name }}</option>

                        @if ($data->children)
                            @foreach($data->children as $datasub)
                                <?php $selected = ($inputSection == $datasub->id) ? ' selected' : ''; ?>

                                <option value="{{ $datasub->id }}"{{ $selected }}>– {{ $datasub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('section') !!}
            </div>

            Искать:<br>
            <?php $inputWhere = getInput('where'); ?>
            <div class="radio">
                <label>
                    <input type="radio" name="where" value="0"{{ $inputWhere == 0 ? ' checked' : '' }}>
                    В названии
                </label>
            </div>

            <div class="radio">
                <label>
                    <input type="radio" name="where" value="1"{{ $inputWhere == 1 ? ' checked' : '' }}>
                    В описании
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
