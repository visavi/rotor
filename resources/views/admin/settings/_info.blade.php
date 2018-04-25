<h3>Вывод информации</h3>

<form action="/admin/settings?act=info" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <?php $inputSite = getInput('sets.incount', $settings['incount']); ?>
    <?php $statsite = ['Выключить', 'Хосты | Хосты всего', 'Хиты | Хиты всего', 'Хиты | Хосты', 'Хиты всего | Хосты всего', 'Графический']; ?>

    <div class="form-group{{ hasError('sets[incount]') }}">
        <label for="incount">Отображение счетчика:</label>
        <select class="form-control" id="incount" name="sets[incount]">

            @foreach ($statsite as $key => $stat)
                <?php $selected = ($key == $settings['incount']) ? ' selected' : ''; ?>
                <option value="{{ $key }}"{{ $selected }}>{{ $stat }}</option>
            @endforeach

        </select>
        {!! textError('sets[incount]') !!}
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input type="hidden" value="0" name="sets[performance]">
            <input name="sets[performance]" class="form-check-input" type="checkbox" value="1"{{ getInput('sets.performance', $settings['performance']) ? ' checked' : '' }}>
            Производительность
        </label>
    </div>

    <div class="form-check">
        <label class="form-check-label">
            <input type="hidden" value="0" name="sets[onlines]">
            <input name="sets[onlines]" class="form-check-input" type="checkbox" value="1"{{ getInput('sets.onlines', $settings['onlines']) ? ' checked' : '' }}>
            Онлайн
        </label>
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
