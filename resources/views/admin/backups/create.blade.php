@extends('layout')

@section('title')
    {{ trans('admin.backup.create_backup') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/backups">{{ trans('index.backup') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('admin.backup.create_backup') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($tables)
        {{ trans('admin.backup.total_tables') }}: <b>{{ count($tables) }}</b><br><br>

        <div class="form">
            <form action="/admin/backups/create" method="post">
                @csrf
                <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked"> <b><label for="all">{{ trans('main.select_all') }}</label></b>

                <?php $sheets = getInput('sheets', []); ?>
                @foreach ($tables as $data)
                    <?php $checked = in_array($data->Name, $sheets, true) ? ' checked' : ''; ?>

                    <div class="form-check">
                        <label class="form-check-label">
                            <input name="sheets[]" class="form-check-input" type="checkbox" value="{{ $data->Name }}"{{ $checked }}>
                            <i class="fa fa-database"></i> <b>{{ $data->Name }}</b> ({{ trans('admin.backup.records') }}: {{ $data->Rows }} / {{ trans('admin.backup.size') }}: {{ formatSize($data->Data_length) }})
                        </label>
                    </div>
                @endforeach

                <?php $inputMethod = getInput('method', 'gzip'); ?>

                <br>
                <div class="form-group{{ hasError('method') }}">
                    <label for="method">{{ trans('admin.backup.compress_method') }}:</label>
                    <select class="form-control" id="method" name="method">

                        <option value="none">{{ trans('admin.backup.not_compress') }}</option>

                        @if ($gzopen)
                            <?php $selected = $inputMethod === 'gzip' ? ' selected' : ''; ?>
                            <option value="gzip"{{ $selected }}>GZip</option>
                        @endif

                        @if ($bzopen)
                            <?php $selected = $inputMethod === 'bzip' ? ' selected' : ''; ?>
                            <option value="bzip"{{ $selected }}>BZip2</option>
                        @endif
                    </select>
                    <div class="invalid-feedback">{{ textError('method') }}</div>
                </div>

                <?php $inputLevel = (int) getInput('level', 7); ?>

                <div class="form-group">
                    <label for="level">{{ trans('admin.backup.compress_ratio') }}:</label>
                    <select class="form-control" id="level" name="level">
                        @foreach($levels as $key => $level)
                            <?php $selected = ($key === $inputLevel) ? ' selected' : ''; ?>
                            <option value="{{ $key }}"{{ $selected }}>{{ $level }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">{{ textError('level') }}</div>
                </div>

                <button class="btn btn-primary">{{ trans('main.create') }}</button>
            </form>
        </div><br>
    @else
        {!! showError(trans('admin.backup.empty_tables')) !!}
    @endif
@stop
