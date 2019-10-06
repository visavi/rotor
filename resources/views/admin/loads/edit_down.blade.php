@extends('layout')

@section('title')
    {{ __('loads.edit_down') }} {{ $down->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/loads">{{ __('index.loads') }}</a></li>

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/admin/loads/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/loads/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.edit_down') }}</li>
            <li class="breadcrumb-item"><a href="/downs/{{ $down->id }}">{{ __('main.review') }}</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if (! $down->active)
        <div class="p-1 bg-warning text-dark">
            <i class="fas fa-exclamation-triangle"></i> {{ __('loads.pending_down1') }}
        </div><br>
    @endif

    @if (isAdmin('boss'))
        <i class="fa fa-pencil-alt"></i>

        @if ($down->active)
            <a href="/admin/downs/publish/{{ $down->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('loads.confirm_unpublish_down') }}')">{{ __('main.unpublish') }}</a> /
        @else
            <a href="/admin/downs/publish/{{ $down->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('loads.confirm_publish_down') }}')">{{ __('main.publish') }}</a> /
        @endif
        <a href="/admin/downs/delete/{{ $down->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('loads.confirm_delete_down') }}')">{{ __('main.delete') }}</a><br>
    @endif

    <div class="form mb-3">
        <form action="/admin/downs/edit/{{ $down->id }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group{{ hasError('category') }}">
                {{ __('main.author') }}: {!! $down->user->getProfile() !!} ({{ dateFixed($down->created_at) }})<br><br>

                <label for="inputCategory">{{ __('loads.load') }}:</label>

                <?php $inputCategory = getInput('category', $down->category_id); ?>
                <select class="form-control" id="inputCategory" name="category">
                    @foreach ($categories as $category)

                        <option value="{{ $category->id }}"{{ ($inputCategory === $category->id && ! $category->closed) ? ' selected' : '' }}{{ $category->closed ? ' disabled' : '' }}>{{ $category->name }}</option>

                        @if ($category->children->isNotEmpty())
                            @foreach($category->children as $datasub)
                                <option value="{{ $datasub->id }}"{{ $inputCategory === $datasub->id && ! $datasub->closed ? ' selected' : '' }}{{ $datasub->closed ? ' disabled' : '' }}>– {{ $datasub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('category') }}</div>
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="title">{{ __('loads.down_title') }}:</label>
                <input class="form-control" name="title" id="title" maxlength="50" value="{{ getInput('title', $down->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ __('loads.down_text') }}:</label>
                <textarea class="form-control markItUp" id="text" name="text" rows="5">{{ getInput('text', $down->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            @if ($down->getFiles()->isNotEmpty())
                @foreach ($down->getFiles() as $file)
                <i class="fa fa-download"></i>
                <b><a href="{{ $file->hash }}">{{ $file->name }}</a></b> ({{ formatSize($file->size) }}) (<a href="/admin/downs/delete/{{ $down->id }}/{{ $file->id }}" onclick="return confirm('{{ __('loads.confirm_delete_file') }}')">{{ __('main.delete') }}</a>)<br>
                @endforeach
            @endif

            @if ($down->getImages()->isNotEmpty())
                @foreach ($down->getImages() as $image)
                    {!! resizeImage($image->hash) !!}<br>
                    <i class="fa fa-image"></i> <b><a href="{{ $image->hash }}">{{ $image->name }}</a></b> ({{ formatSize($image->size ) }}) (<a href="/admin/downs/delete/{{ $down->id }}/{{ $image->id }}" onclick="return confirm('{{ __('loads.confirm_delete_screen') }}')">{{ __('main.delete') }}</a>)<br><br>
                @endforeach
            @endif

            @if ($down->files->count() < setting('maxfiles'))
                <div class="custom-file{{ hasError('files') }}">
                    <label class="btn btn-sm btn-secondary" for="files">
                        <input type="file" id="files" name="files[]" onchange="$('#upload-file-info').html((this.files.length > 1) ? '{{ __('main.files') }}: ' + this.files.length : this.files[0].name);" hidden multiple>
                        {{ __('main.attach_files') }}&hellip;
                    </label>
                    <span class="badge badge-info" id="upload-file-info"></span>
                    <div class="invalid-feedback">{{ textError('files') }}</div>
                </div>
            @endif

            <p class="text-muted font-italic">
                {{ __('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
                {{ __('main.max_file_weight') }}: {{ formatSize(setting('fileupload')) }}<br>
                {{ __('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('allowextload')) }}<br>
                {{ __('main.min_image_size') }}: 100px
            </p>

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
