@section('header')
    <h1>{{ __('settings.loads') }}</h1>
@stop

<form action="/admin/settings?act=loads" method="post">
    @csrf
    <div class="form-group{{ hasError('sets[maxfiles]') }}">
        <label for="maxfiles">{{ __('settings.loads_max_files') }}:</label>
        <input type="number" class="form-control" id="maxfiles" name="sets[maxfiles]" maxlength="2" value="{{ getInput('sets.maxfiles', $settings['maxfiles']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[maxfiles]') }}</div>
    </div>


    <div class="form-group{{ hasError('sets[downlist]') }}">
        <label for="downlist">{{ __('settings.loads_per_page') }}:</label>
        <input type="number" class="form-control" id="downlist" name="sets[downlist]" maxlength="2" value="{{ getInput('sets.downlist', $settings['downlist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[downlist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[ziplist]') }}">
        <label for="ziplist">{{ __('settings.loads_archives') }}:</label>
        <input type="number" class="form-control" id="ziplist" name="sets[ziplist]" maxlength="2" value="{{ getInput('sets.ziplist', $settings['ziplist']) }}" required>
        <div class="invalid-feedback">{{ textError('sets[ziplist]') }}</div>
    </div>

    <div class="form-group{{ hasError('sets[fileupload]') }}">
        <label for="fileupload">{{ __('main.max_file_weight') }} (Mb):</label>
        <input type="number" class="form-control" id="fileupload" name="sets[fileupload]" maxlength="3" value="{{ getInput('sets.fileupload', round($settings['fileupload'] / 1048576)) }}" required>
        <div class="invalid-feedback">{{ textError('sets[fileupload]') }}</div>

        <input type="hidden" value="1048576" name="mods[fileupload]">
        <span class="text-muted font-italic">{{ __('main.server_limit') }}: {{ ini_get('upload_max_filesize') }}</span>
    </div>

    <div class="form-group{{ hasError('sets[allowextload]') }}">
        <label for="allowextload">{{ __('main.valid_file_extensions') }}:</label>
        <textarea class="form-control" id="allowextload" name="sets[allowextload]" required>{{ getInput('sets.allowextload', $settings['allowextload']) }}</textarea>
        <div class="invalid-feedback">{{ textError('sets[allowextload]') }}</div>
    </div>

    <div class="custom-control custom-checkbox">
        <input type="hidden" value="0" name="sets[downupload]">
        <input type="checkbox" class="custom-control-input" value="1" name="sets[downupload]" id="downupload"{{ getInput('sets.downupload', $settings['downupload']) ? ' checked' : '' }}>
        <label class="custom-control-label" for="downupload">{{ __('settings.loads_files_allow') }}</label>
    </div>

    <button class="btn btn-primary">{{ __('main.save') }}</button>
</form>
