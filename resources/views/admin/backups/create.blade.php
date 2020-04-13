@extends('layout')

@section('title')
    {{ __('admin.backup.create_backup') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/backups">{{ __('index.backup') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.backup.create_backup') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($tables)
        {{ __('admin.backup.total_tables') }}: <b>{{ count($tables) }}</b><br><br>

        <div class="section-form p-2 shadow">
            <form action="/admin/backups/create" method="post">
                @csrf
                <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked"> <b><label for="all">{{ __('main.select_all') }}</label></b>

                <?php $sheets = getInput('sheets', []); ?>
                @foreach ($tables as $data)
                    <?php $checked = in_array($data->Name, $sheets, true) ? ' checked' : ''; ?>

                    <div class="form-check">
                        <label class="form-check-label">
                            <input name="sheets[]" class="form-check-input" type="checkbox" value="{{ $data->Name }}"{{ $checked }}>
                            <i class="fa fa-database"></i> <b>{{ $data->Name }}</b> ({{ __('admin.backup.records') }}: {{ $data->Rows }} / {{ __('admin.backup.size') }}: {{ formatSize($data->Data_length) }})
                        </label>
                    </div>
                @endforeach

                <?php $inputMethod = getInput('method', 'gzip'); ?>

                <br>
                <div class="form-group{{ hasError('method') }}">
                    <label for="method">{{ __('admin.backup.compress_method') }}:</label>
                    <select class="form-control" id="method" name="method">

                        <option value="none">{{ __('admin.backup.not_compress') }}</option>

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
                    <label for="level">{{ __('admin.backup.compress_ratio') }}:</label>
                    <select class="form-control" id="level" name="level">
                        @foreach($levels as $key => $level)
                            <?php $selected = ($key === $inputLevel) ? ' selected' : ''; ?>
                            <option value="{{ $key }}"{{ $selected }}>{{ $level }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">{{ textError('level') }}</div>
                </div>

                <button class="btn btn-primary">{{ __('main.create') }}</button>
            </form>
        </div>
    @else
        {!! showError(__('admin.backup.empty_tables')) !!}
    @endif
@stop
