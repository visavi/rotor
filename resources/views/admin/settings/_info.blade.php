@section('header')
    <h1>Вывод информации</h1>
@stop

<form action="/admin/settings?act=info" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <?php $inputSite = getInput('sets.incount', $settings['incount']); ?>
    <?php $statsite = ['Выключить', 'Хосты | Хосты всего', 'Хиты | Хиты всего', 'Хиты | Хосты', 'Хиты всего | Хосты всего', 'Графический']; ?>

    <div class="form-group{{ hasError('sets[incount]') }}">
        <label for="incount">Отображение счетчика:</label>
        <select class="form-control" id="incount" name="sets[incount]">

            @foreach ($statsite as $key => $stat)
                <?php $selected = ($key === (int) $settings['incount']) ? ' selected' : ''; ?>
                <option value="{{ $key }}"{{ $selected }}>{{ $stat }}</option>
            @endforeach

        </select>
        {!! textError('sets[incount]') !!}
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[performance]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[performance]" id="performance"{{ getInput('sets.performance', $settings['performance']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="performance">Производительность</label>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[onlines]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[onlines]" id="onlines"{{ getInput('sets.onlines', $settings['onlines']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="onlines">Онлайн</label>
    </div>

    <div class="form-group{{ hasError('sets[timeonline]') }}">
        <label for="timeonline">Время подсчета онлайн (минут):</label>
        <input type="number" class="form-control" id="timeonline" name="sets[timeonline]" maxlength="3" value="{{ getInput('sets.timeonline', round($settings['timeonline'] / 60)) }}" required>
        {!! textError('sets[timeonline]') !!}

        <input type="hidden" value="60" name="mods[timeonline]">
    </div>

    <p class="text-muted font-italic">
        На сколько минут запоминать IP пользователя
    </p>

    <button class="btn btn-primary">Сохранить</button>
</form>
