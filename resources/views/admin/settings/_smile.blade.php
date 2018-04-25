<h3>Смайлы</h3>

<form action="/admin/settings?act=smile" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="form-group{{ hasError('sets[smilemaxsize]') }}">
        <label for="smilemaxsize">Максимальный вес смайла (kb):</label>
        <input type="number" class="form-control" id="smilemaxsize" name="sets[smilemaxsize]" maxlength="3" value="{{ getInput('sets.smilemaxsize', round($settings['smilemaxsize'] / 1024)) }}" required>
        {!! textError('sets[smilemaxsize]') !!}

        <input type="hidden" value="1024" name="mods[smilemaxsize]">
    </div>

    <div class="form-group{{ hasError('sets[smilemaxweight]') }}">
        <label for="smilemaxweight">Максимальный размер смайла (px):</label>
        <input type="number" class="form-control" id="smilemaxweight" name="sets[smilemaxweight]" maxlength="3" value="{{ getInput('sets.smilemaxweight', $settings['smilemaxweight']) }}" required>
        {!! textError('sets[smilemaxweight]') !!}
    </div>

    <div class="form-group{{ hasError('sets[smileminweight]') }}">
        <label for="smileminweight">Минимальный размер смайла (px):</label>
        <input type="number" class="form-control" id="smileminweight" name="sets[smileminweight]" maxlength="3" value="{{ getInput('sets.smileminweight', $settings['smileminweight']) }}" required>
        {!! textError('sets[smileminweight]') !!}
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
