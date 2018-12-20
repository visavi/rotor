@extends('layout')

@section('title')
    Поиск по файлам
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">Загрузки</a></li>
            <li class="breadcrumb-item active">Поиск</li>
        </ol>
    </nav>

    <h1>Поиск по файлам</h1>

    <div class="form">
        <form action="/loads/search">
            <input type="hidden" name="cid" value="{{ $cid }}">

            <div class="form-group{{ hasError('find') }}">
                <label for="inputFind">Запрос</label>
                <input name="find" class="form-control" id="inputFind" maxlength="50" placeholder="Введите запрос" value="{{ getInput('find') }}" required>
                {!! textError('find') !!}
            </div>

            <div class="form-group{{ hasError('section') }}">
                <label for="inputSection">Раздел</label>
                <?php $inputSection = (int) getInput('section', $cid); ?>

                <select class="form-control" id="inputSection" name="section">
                    <option value="0">Не имеет значения</option>

                    @foreach ($categories as $data)
                        <?php $selected = ($inputSection === $data->id) ? ' selected' : ''; ?>
                        <option value="{{ $data->id }}"{{ $selected }}>{{ $data->name }}</option>

                        @if ($data->children)
                            @foreach($data->children as $datasub)
                                <?php $selected = ($inputSection === $datasub->id) ? ' selected' : ''; ?>
                                <option value="{{ $datasub->id }}"{{ $selected }}>– {{ $datasub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('section') !!}
            </div>

            Искать:<br>
            <?php $inputWhere = (int) getInput('where'); ?>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputWhere0" name="where" value="0"{{ $inputWhere === 0 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputWhere0">В названии</label>
            </div>
            <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" id="inputWhere1" name="where" value="1"{{ $inputWhere === 1 ? ' checked' : '' }}>
                <label class="custom-control-label" for="inputWhere1">В описании</label>
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
