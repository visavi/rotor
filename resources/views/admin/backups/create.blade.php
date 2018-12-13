@extends('layout')

@section('title')
    Создание backup
@stop

@section('content')

    <h1>Создание backup</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/backups">Backup</a></li>
            <li class="breadcrumb-item active">Создание backup</li>
        </ol>
    </nav>

    @if ($tables)
        Всего таблиц: <b>{{ count($tables) }}</b><br><br>

        <div class="form">
            <form action="/admin/backups/create" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">


                <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked"> <b><label for="all">Отметить все</label></b><hr>

                <?php $sheets = getInput('sheets', []); ?>
                @foreach ($tables as $data)
                    <?php $checked = in_array($data->Name, $sheets, true) ? ' checked' : ''; ?>

                    <div class="form-check">
                        <label class="form-check-label">
                            <input name="sheets[]" class="form-check-input" type="checkbox" value="{{ $data->Name }}"{{ $checked }}>
                            <i class="fa fa-database"></i> <b>{{ $data->Name }}</b> (Записей: {{ $data->Rows }} / Размер: {{ formatSize($data->Data_length) }})
                        </label>
                    </div>
                @endforeach

                <?php $inputMethod = getInput('method', 'gzip'); ?>

                <br>
                <div class="form-group{{ hasError('method') }}">
                    <label for="method">Метод сжатия:</label>
                    <select class="form-control" id="method" name="method">

                        <option value="none">Не сжимать</option>

                        @if ($gzopen)
                            <?php $selected = $inputMethod === 'gzip' ? ' selected' : ''; ?>
                            <option value="gzip"{{ $selected }}>GZip</option>
                        @endif

                        @if ($bzopen)
                            <?php $selected = $inputMethod === 'bzip' ? ' selected' : ''; ?>
                            <option value="bzip"{{ $selected }}>BZip2</option>
                        @endif
                    </select>
                    {!! textError('method') !!}
                </div>

                <?php $inputLevel = (int) getInput('level', 7); ?>

                <div class="form-group">
                    <label for="level">Степень сжатия:</label>
                    <select class="form-control" id="level" name="level">
                        @foreach($levels as $key => $level)
                            <?php $selected = ($key === $inputLevel) ? ' selected' : ''; ?>
                            <option value="{{ $key }}"{{ $selected }}>{{ $level }}</option>
                        @endforeach
                    </select>
                    {!! textError('level') !!}
                </div>

                <button class="btn btn-primary">Выполнить</button>
            </form>
        </div><br>
    @else
        {!! showError('Нет таблиц для бэкапа!') !!}
    @endif
@stop
