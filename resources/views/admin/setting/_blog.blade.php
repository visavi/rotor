<h3>Блоги</h3>

<form action="/admin/setting?act=blog" method="post">
    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

    <div class="form-group{{ hasError('sets[blogpost]') }}">
        <label for="blogpost">Статей на страницу:</label>
        <input type="number" class="form-control" id="blogpost" name="sets[blogpost]" maxlength="2" value="{{ getInput('sets[blogpost]', $settings['blogpost']) }}" required>
        {!! textError('sets[blogpost]') !!}
    </div>

    <div class="form-group{{ hasError('sets[blogcomm]') }}">
        <label for="blogcomm">Комментариев в блогах на стр.:</label>
        <input type="number" class="form-control" id="blogcomm" name="sets[blogcomm]" maxlength="2" value="{{ getInput('sets[blogcomm]', $settings['blogcomm']) }}" required>
        {!! textError('sets[blogcomm]') !!}
    </div>

    <div class="form-group{{ hasError('sets[bloggroup]') }}">
        <label for="bloggroup">Группы блогов на стр.:</label>
        <input type="number" class="form-control" id="bloggroup" name="sets[bloggroup]" maxlength="2" value="{{ getInput('sets[bloggroup]', $settings['bloggroup']) }}" required>
        {!! textError('sets[bloggroup]') !!}
    </div>

    <div class="form-group{{ hasError('sets[maxblogpost]') }}">
        <label for="maxblogpost">Кол. символов в статье:</label>
        <input type="number" class="form-control" id="maxblogpost" name="sets[maxblogpost]" maxlength="6" value="{{ getInput('sets[maxblogpost]', $settings['maxblogpost']) }}" required>
        {!! textError('sets[maxblogpost]') !!}
    </div>

    <div class="form-group{{ hasError('sets[blogvotepoint]') }}">
        <label for="blogvotepoint">Актива для голосования за статьи:</label>
        <input type="number" class="form-control" id="blogvotepoint" name="sets[blogvotepoint]" maxlength="3" value="{{ getInput('sets[blogvotepoint]', $settings['blogvotepoint']) }}" required>
        {!! textError('sets[blogvotepoint]') !!}
    </div>

    <button class="btn btn-primary">Сохранить</button>
</form>
