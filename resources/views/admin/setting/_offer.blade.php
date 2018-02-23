<h3>Предложения / Проблемы</h3>

<form action="/admin/setting?act=offer" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="form-group{{ hasError('sets[postoffers]') }}">
        <label for="postoffers">Предложений на страницу:</label>
        <input type="number" class="form-control" id="postoffers" name="sets[postoffers]" maxlength="2" value="{{ getInput('sets[postoffers]', $settings['postoffers']) }}" required>
        {!! textError('sets[postoffers]') !!}
    </div>

    <div class="form-group{{ hasError('sets[postcommoffers]') }}">
        <label for="postcommoffers">Комментариев на страницу:</label>
        <input type="number" class="form-control" id="postcommoffers" name="sets[postcommoffers]" maxlength="2" value="{{ getInput('sets[postcommoffers]', $settings['postcommoffers']) }}" required>
        {!! textError('sets[postcommoffers]') !!}
    </div>

    <div class="form-group{{ hasError('sets[addofferspoint]') }}">
        <label for="addofferspoint">Актива для создания предложения или проблемы:</label>
        <input type="number" class="form-control" id="addofferspoint" name="sets[addofferspoint]" maxlength="4" value="{{ getInput('sets[addofferspoint]', $settings['addofferspoint']) }}" required>
        {!! textError('sets[addofferspoint]') !!}
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
